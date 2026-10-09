import test from 'node:test';
import assert from 'node:assert/strict';
import { newNetworkSummary, recordHttpResponse, diagnoseAccess } from './access-diagnostics.mjs';

test('never stores URL or request headers and counts HTTP errors by type', () => {
  const s = newNetworkSummary();
  recordHttpResponse(s, 200, 'document', true);
  recordHttpResponse(s, 403, 'fetch');
  recordHttpResponse(s, 404, 'script');
  recordHttpResponse(s, 503, 'media');
  assert.equal(s.mainHttpStatus, 200);
  assert.equal(s.fetchErrors, 1);
  assert.equal(s.scriptErrors, 1);
  assert.equal(s.mediaErrors, 1);
  assert.deepEqual(Object.keys(s).sort(), [
    'documentErrors', 'failedRequests', 'fetchErrors', 'mainHttpStatus',
    'mediaErrors', 'otherErrors', 'scriptErrors', 'xhrErrors',
  ]);
});

test('403 means recorder access failure, not broadcaster offline', () => {
  const s = newNetworkSummary();
  recordHttpResponse(s, 403, 'document', true);
  const r = diagnoseAccess({ kind: 'unknown', detail: 'Geen video' }, s);
  assert.equal(r.kind, 'error');
  assert.match(r.detail, /HTTP 403/);
  assert.match(r.detail, /kan wel live zijn/);
});

test('HTTP 200 with no media remains unknown and exposes safe request counts', () => {
  const s = newNetworkSummary();
  recordHttpResponse(s, 200, 'document', true);
  recordHttpResponse(s, 403, 'xhr');
  const r = diagnoseAccess({ kind: 'unknown', detail: 'Geen video' }, s);
  assert.equal(r.kind, 'unknown');
  assert.match(r.detail, /pagina HTTP 200/);
  assert.match(r.detail, /XHR\/fetch HTTP-fouten 1/);
});

test('verified video and age gate are never overridden by a navigation error', () => {
  const s = newNetworkSummary();
  recordHttpResponse(s, 403, 'document', true);
  for (const kind of ['live', 'needs_setup', 'recording']) {
    const input = { kind, detail: 'verified' };
    assert.deepEqual(diagnoseAccess(input, s), input);
  }
});

test('explicit offline with successful page remains offline', () => {
  const s = newNetworkSummary();
  recordHttpResponse(s, 200, 'document', true);
  const input = { kind: 'offline', detail: 'De pagina toont offline' };
  assert.deepEqual(diagnoseAccess(input, s), input);
});
