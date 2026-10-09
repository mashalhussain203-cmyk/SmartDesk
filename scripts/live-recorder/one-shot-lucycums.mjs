/**
 * Single authorized, server-side 30-second test capture for the owner's room.
 * No browser sharing, authentication bypass, or third-party upload links.
 * Never log or persist signed HLS source URLs.
 *
 * A private S3 marker prevents a second recording after successful completion.
 */
import { spawn } from 'node:child_process';
import { setTimeout as delay } from 'node:timers/promises';
import { PutObjectCommand, HeadObjectCommand, DeleteObjectCommand } from '@aws-sdk/client-s3';
import { Upload } from '@aws-sdk/lib-storage';
import { findPublicMedia, hlsFfmpegArgs } from './public-hls.mjs';

const target = 'lucycums';
const marker = 'status/one-shot-lucycums-20261009.json';

export function thirtySecondArgs(urls) {
  const args = hlsFfmpegArgs(urls);
  const at = args.indexOf('-movflags');
  if (at < 0) throw new Error('Expected FFmpeg MP4 output');
  args.splice(at, 0, '-t', '30');
  return args;
}

async function status(s3, bucket, state, message) {
  const checked_at = new Date().toISOString();
  await s3.send(new PutObjectCommand({
    Bucket: bucket, Key: 'status/' + target + '.json',
    Body: JSON.stringify({ account: target, status: state, message, checked_at }),
    ContentType: 'application/json', CacheControl: 'no-store',
  }));
  console.log(checked_at, target, state, message);
}

export async function recordLucycumsOnce({ s3, bucket, discover = findPublicMedia, retryDelay = delay }) {
  try {
    await s3.send(new HeadObjectCommand({ Bucket: bucket, Key: marker }));
    console.log(target, 'one-shot already saved in private S3; skipping');
    return { kind: 'already-saved' };
  } catch (error) {
    if (error?.$metadata?.httpStatusCode !== 404 &&
        error?.name !== 'NotFound' && error?.name !== 'NoSuchKey') {
      throw new Error('Unable to check private one-shot marker');
    }
  }

  let found;
  for (let attempt = 1; attempt <= 3; attempt++) {
    found = await discover(target);
    if (found.kind === 'live' && Array.isArray(found.urls) && found.urls.length >= 2) break;
    console.log(target, 'public stream not playable (attempt ' + attempt + '/3)');
    if (attempt < 3) await retryDelay(8000);
  }
  if (found?.kind !== 'live' || !Array.isArray(found.urls) || found.urls.length < 2) {
    await status(s3, bucket, 'unknown', '30-secondenproef niet opgeslagen: geen toegankelijke livestream');
    return { kind: 'unavailable' };
  }

  const filename = new Date().toISOString().replace(/[:.]/g, '-') + '_lucycums.mp4';
  const key = 'recordings/' + target + '/' + filename;
  const args = thirtySecondArgs(found.urls);
  await status(s3, bucket, 'recording', '30 seconden publieke video en audio worden privé opgenomen');
  // The source URLs are passed only as child-process arguments, never printed.
  const proc = spawn('ffmpeg', args, { stdio: ['ignore', 'pipe', 'ignore'] });
  const timer = setTimeout(() => proc.kill('SIGKILL'), 90000);
  const exited = new Promise((resolve, reject) => {
    proc.once('error', reject);
    proc.once('exit', (code, signal) => resolve({ code, signal }));
  });
  const upload = new Upload({
    client: s3,
    params: { Bucket: bucket, Key: key, Body: proc.stdout,
      ContentType: 'video/mp4', CacheControl: 'private, no-store' },
    queueSize: 2, partSize: 8 * 1024 * 1024, leavePartsOnError: false,
  });
  const uploaded = upload.done().then(() => ({ ok: true }), error => ({ ok: false, error }));
  let saved = false;
  try {
    const exit = await exited;
    const result = await uploaded;
    if (exit.code !== 0 || !result.ok) {
      throw new Error('FFmpeg stream or private S3 upload did not complete');
    }
    const head = await s3.send(new HeadObjectCommand({ Bucket: bucket, Key: key }));
    if (!head.ContentLength || head.ContentLength < 100000) {
      throw new Error('Private MP4 is unexpectedly small');
    }
    const bytes = head.ContentLength;
    await s3.send(new PutObjectCommand({
      Bucket: bucket, Key: marker,
      Body: JSON.stringify({ account: target, key, bytes, recorded_at: new Date().toISOString() }),
      ContentType: 'application/json', CacheControl: 'no-store',
    }));
    saved = true;
    await status(s3, bucket, 'live', 'Opname opgeslagen: ' + filename);
    console.log(target, 'ONE_SHOT_SAVED_S3', filename, bytes, 'bytes');
    return { kind: 'saved', key, bytes };
  } finally {
    clearTimeout(timer);
    if (proc.exitCode === null) proc.kill('SIGKILL');
    if (!saved) {
      await s3.send(new DeleteObjectCommand({ Bucket: bucket, Key: key })).catch(() => {});
    }
  }
}
