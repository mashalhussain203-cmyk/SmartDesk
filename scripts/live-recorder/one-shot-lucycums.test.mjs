import test from 'node:test';
import assert from 'node:assert/strict';
import { thirtySecondArgs, recordLucycumsOnce } from './one-shot-lucycums.mjs';

const urls = ['https://a.mmcdn.com/video.m3u8?token=secret', 'https://a.mmcdn.com/audio.m3u8?token=secret'];

test('one-shot copies HLS video and audio and caps output at 30 seconds', () => {
  const args = thirtySecondArgs(urls);
  assert.deepEqual(args.slice(args.indexOf('-t'), args.indexOf('-t') + 2), ['-t', '30']);
  assert.deepEqual(args.slice(args.indexOf('-map'), args.indexOf('-map') + 4),
    ['-map', '0:v:0', '-map', '1:a:0']);
  assert.ok(args.includes('copy'));
  assert.equal(args.at(-1), 'pipe:1');
});

test('existing private S3 marker prevents duplicate capture', async () => {
  const requests = [];
  const s3 = { async send(command) { requests.push(command.constructor.name); return {}; } };
  const result = await recordLucycumsOnce({
    s3, bucket: 'private',
    discover: () => { throw new Error('must not access the stream'); },
  });
  assert.equal(result.kind, 'already-saved');
  assert.deepEqual(requests, ['HeadObjectCommand']);
});

test('unavailable source never uploads a video or starts FFmpeg', async () => {
  const requests = [];
  const s3 = { async send(command) {
    requests.push(command.constructor.name);
    if (command.constructor.name === 'HeadObjectCommand') {
      const error = new Error('NotFound');
      error.$metadata = { httpStatusCode: 404 };
      throw error;
    }
    return {};
  }};
  const result = await recordLucycumsOnce({
    s3, bucket: 'private', discover: async () => ({ kind: 'unknown' }), retryDelay: async () => {},
  });
  assert.equal(result.kind, 'unavailable');
  assert.deepEqual(requests, ['HeadObjectCommand', 'PutObjectCommand']);
});
