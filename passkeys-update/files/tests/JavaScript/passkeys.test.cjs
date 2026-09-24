const {test} = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
const path = require('node:path');
const source = fs.readFileSync(path.join(__dirname, '../../public/js/mashal-passkeys.js'), 'utf8');

async function setup({iphone = true, supported = true, canceled = false, mode = 'login', httpStatus = 200} = {}) {
    const elements = {};
    function element() {
        return {hidden: false, disabled: false, textContent: '', value: 'Mijn iPhone', children: [],
            addEventListener(event, fn) {this[event] = fn;},
            setAttribute() {}, append(...items) {this.children.push(...items);},
            replaceChildren() {this.children = [];}};
    }
    for (const name of ['status', 'start', 'enroll', 'device-note', 'list', 'name']) elements[name] = element();
    const root = {hidden: true, dataset: {mode, iphoneOnly: 'true', options: '/options', verify: '/verify', list: '/list'},
        querySelector(selector) {return elements[selector.replace('[data-passkey-', '').replace(']', '')];}};
    const requests = [];
    let receivedOptions;
    let redirect;
    const credential = {rawId: new Uint8Array([1, 2, 255]).buffer, type: 'public-key', response: {
        clientDataJSON: new Uint8Array([3, 4]).buffer,
        authenticatorData: new Uint8Array([5, 6]).buffer,
        signature: new Uint8Array([7, 8]).buffer,
        userHandle: new Uint8Array([9, 10]).buffer,
        attestationObject: new Uint8Array([11, 12]).buffer,
    }};
    async function credentialRequest(options) {
        receivedOptions = options;
        if (canceled) {const error = new Error('canceled'); error.name = 'NotAllowedError'; throw error;}
        return credential;
    }
    const context = {
        Uint8Array, Array, Object, String, Boolean, Error, Date, JSON,
        atob: value => Buffer.from(value, 'base64').toString('binary'),
        btoa: value => Buffer.from(value, 'binary').toString('base64'),
        document: {readyState: 'complete', createElement: element,
            querySelector: () => ({content: 'test-csrf'}), querySelectorAll: () => [root]},
        navigator: {userAgent: iphone ? 'iPhone' : 'Android', credentials: {get: credentialRequest, create: credentialRequest}},
        PublicKeyCredential: {isUserVerifyingPlatformAuthenticatorAvailable: async () => supported},
        fetch: async (url, init) => {
            requests.push({url, init});
            let data = url === '/list' ? {passkeys: []} : url === '/options'
                ? {publicKey: {challenge: 'AQID_w', user: {id: 'BAUG'}, excludeCredentials: [{id: 'BwgJ', type: 'public-key'}]}}
                : {redirect: '/home', message: 'Passkey ingesteld'};
            return {ok: httpStatus === 200, status: httpStatus, json: async () => data};
        },
    };
    context.window = {isSecureContext: true, PublicKeyCredential: context.PublicKeyCredential,
        location: {assign: value => {redirect = value;}}, confirm: () => true};
    vm.runInNewContext(source, context);
    await new Promise(resolve => setImmediate(resolve));
    return {root, elements, requests, options: () => receivedOptions, redirect: () => redirect};
}

test('login button is shown for a supported iPhone', async () => {
    const page = await setup(); assert.equal(page.root.hidden, false);
});
test('login button stays hidden for other devices and unsupported authenticators', async () => {
    assert.equal((await setup({iphone: false})).root.hidden, true);
    assert.equal((await setup({supported: false})).root.hidden, true);
});
test('login serializes the signed credential, sends CSRF and redirects after success', async () => {
    const page = await setup(); await page.elements.start.click();
    assert.deepEqual([...page.options().publicKey.challenge], [1, 2, 3, 255]);
    assert.equal(page.requests.length, 2);
    const sent = page.requests[1];
    assert.equal(sent.init.headers['X-CSRF-TOKEN'], 'test-csrf');
    assert.equal(sent.init.credentials, 'same-origin');
    assert.equal(JSON.parse(sent.init.body).id, 'AQL_');
    assert.equal(JSON.parse(sent.init.body).response.userHandle, 'CQo');
    assert.equal(page.redirect(), '/home');
});
test('canceling never sends a verification request and leaves the button usable', async () => {
    const page = await setup({canceled: true}); await page.elements.start.click();
    assert.equal(page.requests.length, 1);
    assert.equal(page.elements.start.disabled, false);
    assert.match(page.elements.status.textContent, /geannuleerd/);
});
test('registration converts user and excluded credential IDs to bytes', async () => {
    const page = await setup({mode: 'manage'}); await page.elements.start.click();
    assert.deepEqual([...page.options().publicKey.user.id], [4, 5, 6]);
    assert.deepEqual([...page.options().publicKey.excludeCredentials[0].id], [7, 8, 9]);
    const sent = JSON.parse(page.requests.find(r => r.url === '/verify').init.body);
    assert.equal(sent.name, 'Mijn iPhone');
    assert.equal(sent.response.attestationObject, 'Cww');
    assert.equal(page.redirect(), undefined);
});
test('an expired CSRF session produces a useful message and no credential request', async () => {
    const page = await setup({httpStatus: 419}); await page.elements.start.click();
    assert.equal(page.options(), undefined);
    assert.equal(page.elements.start.disabled, false);
    assert.match(page.elements.status.textContent, /sessie is verlopen/);
});
