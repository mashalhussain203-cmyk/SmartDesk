import test from 'node:test';
import assert from 'node:assert/strict';
import { frankieRiversThirtySecondArgs, runFrankieRiversOneShot } from './one-shot-mon1-day.mjs';

const urls = ['https://a.mmcdn.com/video.m3u8?secret=hidden', 'https://a.mmcdn.com/audio.m3u8?secret=hidden'];

function fakeS3(saved = false) {
  const commands = [];
  const s3 = { async send(command) {
    commands.push(command.constructor.name);
    if (!saved && command.constructor.name === 'HeadObjectCommand') {
      const error = new Error('not found');
      error.$metadata = { httpStatusCode: 404 };
      throw error;
    }
    return {};
  }};
  return { s3, commands };
}

test('_frankie_rivers one-shot remuxes both HLS tracks into 30-second MP4', () => {
  const args = frankieRiversThirtySecondArgs(urls);
  assert.deepEqual(args.slice(args.indexOf('-t'), args.indexOf('-t') + 2), ['-t', '30']);
  assert.deepEqual(args.slice(args.indexOf('-map'), args.indexOf('-map') + 4), ['-map', '0:v:0', '-map', '1:a:0']);
  assert.ok(args.includes('copy'));
  assert.equal(args.at(-1), 'pipe:1');
});

test('existing private S3 marker prevents duplicate capture', async () => {
  const { s3, commands } = fakeS3(true);
  const result = await runFrankieRiversOneShot({
    report: async () => {},
    s3, bucket: 'private', signal: new AbortController().signal,
    discover: () => { throw new Error('should not access source'); },
  });
  assert.equal(result.kind, 'already-saved');
  assert.deepEqual(commands, ['HeadObjectCommand']);
});

test('no video is uploaded when _frankie_rivers stream is unavailable', async () => {
  const { s3, commands } = fakeS3();
  const controller = new AbortController();
  let polls = 0;
  const result = await runFrankieRiversOneShot({
    report: async () => {},
    s3, bucket: 'private', signal: controller.signal,
    discover: async () => { polls++; return { kind: 'unknown' }; },
    sleep: async () => controller.abort(),
  });
  assert.equal(result.kind, 'stopped');
  assert.equal(polls, 1);
  assert.deepEqual(commands, ['HeadObjectCommand']);
});

test('playable _frankie_rivers stream uploads once, then exits', async () => {
  const { s3, commands } = fakeS3();
  let saved = 0;
  const result = await runFrankieRiversOneShot({
    report: async () => {},
    s3, bucket: 'private', signal: new AbortController().signal,
    discover: async () => ({ kind: 'live', urls }),
    saveClip: async ({ urls: found }) => {
      assert.deepEqual(found, urls);
      saved++;
      return { key: 'recordings/_frankie_rivers/test.mp4', bytes: 123456 };
    },
  });
  assert.equal(result.kind, 'saved');
  assert.equal(saved, 1);
  assert.deepEqual(commands, ['HeadObjectCommand']);
});
