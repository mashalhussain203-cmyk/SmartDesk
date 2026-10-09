import { readFileSync } from 'node:fs';
import assert from 'node:assert/strict';
import test from 'node:test';

const blade = readFileSync(new URL('../../resources/views/admin/live-recordings.blade.php', import.meta.url), 'utf8');

test('admin /live scripts have valid browser JavaScript after Blade interpolation', () => {
  const scripts = [...blade.matchAll(/<script>([\s\S]*?)<\/script>/g)];
  assert.ok(scripts.length >= 2, 'expected live status and capture scripts');
  for (const [, script] of scripts) {
    const compiled = script.replace(/@json\([^\n;]+\)/g, 'null');
    assert.doesNotThrow(() => new Function(compiled));
  }
});

test('tab capture is user initiated, time-limited and saved via private upload', () => {
  assert.match(blade, /getDisplayMedia\(/);
  assert.match(blade, /new MediaRecorder\(/);
  assert.match(blade, /setTimeout\([\s\S]*?30000\)/);
  assert.match(blade, /uploadPrivate\(blob, 'lucycums', format,/);
  assert.match(blade, /stream\?\.getTracks\(\)\.forEach\(track => track\.stop\(\)\)/);
  assert.match(blade, /stream\.getAudioTracks\(\)\.length/);
});
