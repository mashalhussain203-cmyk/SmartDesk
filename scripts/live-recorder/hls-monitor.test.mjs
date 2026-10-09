import test from 'node:test';
import assert from 'node:assert/strict';
import { isPlayable, hlsContinuousArgs, runHlsMonitor, extraAccountFor } from './hls-monitor.mjs';

const source = {
  kind: 'live',
  urls: ['https://a.mmcdn.com/video.m3u8?token=private', 'https://a.mmcdn.com/audio.m3u8?token=private'],
};

test('two Railway services continuously cover all four owner accounts', () => {
  assert.equal(extraAccountFor('knock1knock'), 'lucycums');
  assert.equal(extraAccountFor('emyii'), 'leo_kitty');
  assert.equal(extraAccountFor('lucycums'), null);
  assert.equal(extraAccountFor('leo_kitty'), null);
});

test('only real separate HLS video and audio starts a recording', () => {
  assert.equal(isPlayable(source), true);
  assert.equal(isPlayable({ kind: 'unknown', urls: source.urls }), false);
  assert.equal(isPlayable({ kind: 'live', urls: source.urls.slice(0, 1) }), false);
  assert.equal(isPlayable(null), false);
});

test('continuous recording includes both tracks, no 30-second limit', () => {
  const args = hlsContinuousArgs(source.urls);
  assert.deepEqual(args.slice(args.indexOf('-map'), args.indexOf('-map') + 4),
    ['-map', '0:v:0', '-map', '1:a:0']);
  assert.ok(args.includes('copy'));
  assert.ok(!args.includes('-t'));
  assert.equal(args.at(-1), 'pipe:1');
});

test('every new live starts recording, offline finishes private MP4, and shutdown finalizes', async () => {
  const controller = new AbortController();
  const states = [];
  const started = [];
  const stopped = [];
  let polls = 0;
  const broadcasts = [
    { kind: 'unknown', detail: 'stream niet publiek beschikbaar' },
    source, source,
    { kind: 'unknown', detail: 'stream niet publiek beschikbaar' },
    { kind: 'unknown', detail: 'stream niet publiek beschikbaar' },
    source,
  ];
  await runHlsMonitor({
    s3: {}, bucket: 'private', signal: controller.signal,
    discover: async account => {
      assert.equal(account, 'lucycums');
      return broadcasts[polls] || broadcasts.at(-1);
    },
    startCapture: async ({ account, urls }) => {
      assert.equal(account, 'lucycums');
      assert.deepEqual(urls, source.urls);
      const key = 'recordings/lucycums/stream-' + (started.length + 1) + '.mp4';
      started.push(key);
      return {
        check: async () => false,
        stop: async () => { stopped.push(key); return key; },
      };
    },
    report: async ({ account, state }) => {
      assert.equal(account, 'lucycums');
      states.push(state);
    },
    sleep: async () => {
      polls++;
      if (polls >= broadcasts.length) controller.abort();
    },
  });
  assert.equal(started.length, 2);
  assert.deepEqual(stopped, started);
  assert.ok(states.includes('offline'));
  assert.ok(states.includes('recording'));
  assert.ok(states.includes('uploading'));
});

test('leo_kitty can be monitored independently on the emyii Railway worker', async () => {
  const controller = new AbortController();
  let started = 0;
  let stopped = 0;
  let checked = 0;
  await runHlsMonitor({
    s3: {}, bucket: 'private', account: 'leo_kitty', signal: controller.signal,
    discover: async account => {
      assert.equal(account, 'leo_kitty');
      return source;
    },
    startCapture: async ({ account }) => {
      assert.equal(account, 'leo_kitty');
      started++;
      return {
        check: async () => false,
        stop: async () => { stopped++; return 'recordings/leo_kitty/saved.mp4'; },
      };
    },
    report: async ({ account }) => { assert.equal(account, 'leo_kitty'); },
    sleep: async () => { checked++; if (checked === 2) controller.abort(); },
  });
  assert.equal(started, 1);
  assert.equal(stopped, 1);
});

test('the extra monitor cannot be configured for a different account', async () => {
  const controller = new AbortController();
  await assert.rejects(
    runHlsMonitor({ s3: {}, bucket: 'private', account: 'emyii', signal: controller.signal }),
    /Unexpected extra account/,
  );
});

test('stream end finalizes an MP4 and allows a subsequent live to be recorded', async () => {
  const controller = new AbortController();
  let polls = 0;
  let captures = 0;
  let saved = 0;
  await runHlsMonitor({
    s3: {}, bucket: 'private', signal: controller.signal,
    discover: async () => source,
    startCapture: async () => {
      captures++;
      return {
        check: async () => true,
        stop: async () => { saved++; return 'recordings/lucycums/saved.mp4'; },
      };
    },
    report: async () => {},
    sleep: async () => {
      polls++;
      if (polls >= 4) controller.abort();
    },
  });
  assert.equal(captures, 2);
  assert.equal(saved, 2);
});
