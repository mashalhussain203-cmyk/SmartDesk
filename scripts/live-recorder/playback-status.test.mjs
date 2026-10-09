import test from 'node:test';
import assert from 'node:assert/strict';
import { mergePlaybackStatus } from './playback-status.mjs';

test('explicit emyii offline from public HLS overrides unknown Chrome player', () => {
  assert.deepEqual(mergePlaybackStatus(
    { kind: 'unknown', detail: 'Geen afspelende video' },
    { kind: 'offline', detail: 'HLS-controle: stream niet publiek beschikbaar' },
  ), { kind: 'offline', detail: 'Uitzending offline bevestigd door publieke streamcontrole' });
});

test('403, 429 and other unknown HLS errors must not be marked offline', () => {
  for (const detail of ['HTTP 403', 'HTTP 429', 'time-out']) {
    const merged = mergePlaybackStatus(
      { kind: 'unknown', detail: 'Chrome geen video' },
      { kind: 'unknown', detail },
    );
    assert.equal(merged.kind, 'unknown');
    assert.match(merged.detail, /Chrome geen video/);
  }
});

test('live playback takes precedence over offline HLS and live HLS overrides unknown Chrome', () => {
  assert.equal(mergePlaybackStatus(
    { kind: 'live', detail: 'Chrome speelt af' }, { kind: 'offline', detail: 'offline' },
  ).kind, 'live');
  assert.equal(mergePlaybackStatus(
    { kind: 'unknown', detail: 'Chrome leeg' }, { kind: 'live', detail: 'HLS speelt af' },
  ).kind, 'live');
});
