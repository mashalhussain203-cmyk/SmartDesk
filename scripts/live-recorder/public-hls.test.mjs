import test from 'node:test';
import assert from 'node:assert/strict';
import { parsePublicMediaUrls, hlsFfmpegArgs, findPublicMedia } from './public-hls.mjs';

const video = 'https://edge2-syd.live.mmcdn.com/v1/edge/streams/track-video.m3u8?session=private';
const audio = 'https://edge2-syd.live.mmcdn.com/v1/edge/streams/track-audio.m3u8?session=private';

test('only allows HTTPS media on the known streaming networks', () => {
  assert.deepEqual(parsePublicMediaUrls(video + '\n' + audio + '\n'), [video, audio]);
  assert.deepEqual(parsePublicMediaUrls('http://edge.live.mmcdn.com/a.m3u8'), []);
  assert.deepEqual(parsePublicMediaUrls('https://evil.example/a.m3u8'), []);
  assert.deepEqual(parsePublicMediaUrls('https://mmcdn.com.evil.example/a.m3u8'), []);
  assert.deepEqual(parsePublicMediaUrls('https://127.0.0.1/private'), []);
});

test('FFmpeg remuxes video and audio to a streamable MP4', () => {
  const args = hlsFfmpegArgs([video, audio]);
  assert.deepEqual(args.slice(args.indexOf('-map'), args.indexOf('-c:v')), [
    '-map', '0:v:0', '-map', '1:a:0',
  ]);
  assert.ok(args.includes('+frag_keyframe+empty_moov+default_base_moof'));
  assert.equal(args.at(-1), 'pipe:1');
  assert.throws(() => hlsFfmpegArgs([video]), /separate/);
});

test('yt-dlp public URLs are returned without credentials', async () => {
  const result = await findPublicMedia('lucycums', async (name, args) => {
    assert.equal(name, 'yt-dlp');
    assert.ok(args.includes('--get-url'));
    assert.ok(args.includes('https://chaturbate.com/lucycums/'));
    return { stdout: video + '\n' + audio + '\n' };
  });
  assert.equal(result.kind, 'live');
  assert.deepEqual(result.urls, [video, audio]);
});

test('403 is not mistaken for a broadcaster being offline', async () => {
  const result = await findPublicMedia('lucycums', async () => {
    const e = new Error('failed');
    e.stderr = 'HTTP Error 403: Forbidden';
    throw e;
  });
  assert.equal(result.kind, 'unknown');
  assert.match(result.detail, /403/);
  assert.ok(!('urls' in result));
});

test('explicit broadcaster offline is distinct from an inaccessible public stream', async () => {
  const offline = await findPublicMedia('emyii', async () => {
    const e = new Error('offline');
    e.stderr = 'This model is currently offline';
    throw e;
  });
  assert.equal(offline.kind, 'offline');
  const blocked = await findPublicMedia('emyii', async () => {
    const e = new Error('forbidden');
    e.stderr = 'HTTP Error 403: Forbidden';
    throw e;
  });
  assert.equal(blocked.kind, 'unknown');
});

test('never accepts an unsafe stream URL', async () => {
  const result = await findPublicMedia('lucycums', async () => ({ stdout: video + '\nhttps://evil.example/private\n' }));
  assert.equal(result.kind, 'unknown');
});
