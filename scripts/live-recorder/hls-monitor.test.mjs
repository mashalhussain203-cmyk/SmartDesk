import test from 'node:test';
import assert from 'node:assert/strict';
import { isPlayable, hlsContinuousArgs, runHlsMonitor } from './hls-monitor.mjs';

const source = {
  kind: 'live',
  urls: ['https://a.mmcdn.com/video.m3u8?token=private', 'https://a.mmcdn.com/audio.m3u8?token=private'],
};

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
  const reported = [];
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
      assert.equal(account, 'cutefacebigass');
      return broadcasts[polls] || broadcasts.at(-1);
    },
    startCapture: async ({ account, urls }) => {
      assert.equal(account, 'cutefacebigass');
      assert.deepEqual(urls, source.urls);
      const key = 'recordings/cutefacebigass/stream-' + (started.length + 1) + '.mp4';
      started.push(key);
      return {
        check: async () => false,
        stop: async () => { stopped.push(key); return key; },
      };
    },
    report: async ({ account, state, lastSaved }) => {
      assert.equal(account, 'cutefacebigass');
      states.push(state);
      reported.push({ state, lastSaved });
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
  assert.ok(reported.some(x => x.lastSaved === 'stream-1.mp4'));
  assert.ok(reported.some(x => x.lastSaved === 'stream-2.mp4'));
});

test('unavailable HLS source shows unknown, not falsely offline', async () => {
  const controller = new AbortController();
  const states = [];
  await runHlsMonitor({
    s3: {}, bucket: 'private', account: 'ricasashaa', signal: controller.signal,
    discover: async () => ({ kind: 'unknown', detail: 'HLS-controle: stream niet publiek beschikbaar' }),
    report: async ({ state }) => states.push(state),
    sleep: async () => controller.abort(),
  });
  assert.deepEqual(states, ['unknown', 'unknown']);
});

test('ricasashaa can be monitored independently on the emyii Railway worker', async () => {
  const controller = new AbortController();
  let started = 0;
  let stopped = 0;
  let checked = 0;
  await runHlsMonitor({
    s3: {}, bucket: 'private', account: 'ricasashaa', signal: controller.signal,
    discover: async account => {
      assert.equal(account, 'ricasashaa');
      return source;
    },
    startCapture: async ({ account }) => {
      assert.equal(account, 'ricasashaa');
      started++;
      return {
        check: async () => false,
        stop: async () => { stopped++; return 'recordings/ricasashaa/saved.mp4'; },
      };
    },
    report: async ({ account }) => { assert.equal(account, 'ricasashaa'); },
    sleep: async () => { checked++; if (checked === 2) controller.abort(); },
  });
  assert.equal(started, 1);
  assert.equal(stopped, 1);
});

test('cutefacebigass continuously records complete streams and reports online status', async () => {
  const controller = new AbortController();
  const states = [];
  const started = [];
  const saved = [];
  let poll = 0;
  const sources = [source, source, { kind: 'offline' }, { kind: 'offline' }, source];
  await runHlsMonitor({
    s3: {}, bucket: 'private', account: 'cutefacebigass', signal: controller.signal,
    discover: async account => {
      assert.equal(account, 'cutefacebigass');
      return sources[Math.min(poll, sources.length - 1)];
    },
    startCapture: async ({ account, urls }) => {
      assert.equal(account, 'cutefacebigass');
      assert.deepEqual(urls, source.urls);
      const key = 'recordings/cutefacebigass/full-' + (started.length + 1) + '.mp4';
      started.push(key);
      return {
        check: async () => false,
        stop: async () => { saved.push(key); return key; },
      };
    },
    report: async ({ state }) => states.push(state),
    sleep: async () => { poll++; if (poll >= sources.length) controller.abort(); },
  });
  assert.deepEqual(saved, started);
  assert.equal(started.length, 2);
  assert.ok(states.includes('recording'));
  assert.ok(states.includes('uploading'));
  assert.ok(states.includes('offline'));
});

test('the HLS monitor cannot be configured for an unapproved account', async () => {
  const controller = new AbortController();
  await assert.rejects(
    runHlsMonitor({ s3: {}, bucket: 'private', account: 'unknown_broadcaster', signal: controller.signal }),
    /Unexpected extra account/,
  );
});

test('emyii uses the same continuous HLS capture flow without Chromium navigation', async () => {
  const controller = new AbortController();
  const statuses = [];
  const saved = [];
  let poll = 0;
  let starts = 0;
  await runHlsMonitor({
    s3: {}, bucket: 'private', account: 'emyii', signal: controller.signal,
    discover: async account => {
      assert.equal(account, 'emyii');
      return poll === 0 ? source : { kind: 'offline' };
    },
    startCapture: async ({ account, urls }) => {
      assert.equal(account, 'emyii');
      assert.deepEqual(urls, source.urls);
      assert.equal(hlsContinuousArgs(urls).includes('-t'), false);
      starts++;
      return {
        check: async () => false,
        stop: async () => {
          saved.push('recordings/emyii/complete.mp4');
          return 'recordings/emyii/complete.mp4';
        },
      };
    },
    report: async ({ account, state, lastSaved }) => {
      assert.equal(account, 'emyii');
      statuses.push({ state, lastSaved });
    },
    sleep: async () => {
      poll++;
      if (poll >= 3) controller.abort();
    },
  });
  assert.equal(starts, 1);
  assert.deepEqual(saved, ['recordings/emyii/complete.mp4']);
  assert.ok(statuses.some(item => item.state === 'recording'));
  assert.ok(statuses.some(item => item.state === 'uploading'));
  assert.ok(statuses.some(item => item.lastSaved === 'complete.mp4'));
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
        stop: async () => { saved++; return 'recordings/cutefacebigass/saved.mp4'; },
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

test('cutefacebigass runs as an independent full-length private monitor', async () => {
  const controller = new AbortController();
  let started = 0;
  let saved = 0;
  const statuses = [];
  let polls = 0;
  await runHlsMonitor({
    s3: {}, bucket: 'private', account: 'cutefacebigass', signal: controller.signal,
    discover: async account => {
      assert.equal(account, 'cutefacebigass');
      return polls === 0 ? source : { kind: 'offline' };
    },
    startCapture: async ({ account, urls }) => {
      assert.equal(account, 'cutefacebigass');
      assert.ok(!hlsContinuousArgs(urls).includes('-t'));
      started++;
      return {
        check: async () => false,
        stop: async () => {
          saved++;
          return 'recordings/cutefacebigass/full.mp4';
        },
      };
    },
    report: async ({ state }) => statuses.push(state),
    sleep: async () => {
      polls++;
      if (polls >= 3) controller.abort();
    },
  });
  assert.equal(started, 1);
  assert.equal(saved, 1);
  assert.ok(statuses.includes('recording'));
  assert.ok(statuses.includes('uploading'));
});

test('ricasashaa records full sessions independently and saves the finished MP4', async () => {
  const controller = new AbortController();
  let polls = 0;
  let started = 0;
  let saved = 0;
  const states = [];
  const filenames = [];
  await runHlsMonitor({
    s3: {}, bucket: 'private', account: 'ricasashaa', signal: controller.signal,
    discover: async account => {
      assert.equal(account, 'ricasashaa');
      return polls === 0 ? source : { kind: 'offline' };
    },
    startCapture: async ({ account, urls }) => {
      assert.equal(account, 'ricasashaa');
      assert.ok(!hlsContinuousArgs(urls).includes('-t'));
      started++;
      return {
        check: async () => false,
        stop: async () => {
          saved++;
          return 'recordings/ricasashaa/full.mp4';
        },
      };
    },
    report: async ({ state, lastSaved }) => {
      states.push(state);
      filenames.push(lastSaved);
    },
    sleep: async () => {
      polls++;
      if (polls >= 3) controller.abort();
    },
  });
  assert.equal(started, 1);
  assert.equal(saved, 1);
  assert.ok(states.includes('recording'));
  assert.ok(states.includes('uploading'));
  assert.ok(filenames.includes('full.mp4'));
});

test('julesxdann records an entire playable live, finalizes the private MP4 and keeps its own status', async () => {
  const controller = new AbortController();
  const states = [];
  const saved = [];
  let poll = 0;
  let starts = 0;
  await runHlsMonitor({
    s3: {}, bucket: 'private', account: 'julesxdann', signal: controller.signal,
    discover: async account => {
      assert.equal(account, 'julesxdann');
      return poll === 0 ? source : { kind: 'offline' };
    },
    startCapture: async ({ account, urls }) => {
      assert.equal(account, 'julesxdann');
      assert.deepEqual(urls, source.urls);
      assert.equal(hlsContinuousArgs(urls).includes('-t'), false);
      starts++;
      return {
        check: async () => false,
        stop: async () => {
          const key = 'recordings/julesxdann/complete.mp4';
          saved.push(key);
          return key;
        },
      };
    },
    report: async ({ account, state, lastSaved }) => {
      assert.equal(account, 'julesxdann');
      states.push({ state, lastSaved });
    },
    sleep: async () => {
      poll++;
      if (poll >= 3) controller.abort();
    },
  });
  assert.equal(starts, 1);
  assert.deepEqual(saved, ['recordings/julesxdann/complete.mp4']);
  assert.ok(states.some(item => item.state === 'recording'));
  assert.ok(states.some(item => item.state === 'uploading'));
  assert.ok(states.some(item => item.lastSaved === 'complete.mp4'));
});
