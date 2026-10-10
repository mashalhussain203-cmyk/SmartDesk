/**
 * Owner-authorized 30-second test clip for _frankie_rivers, saved only in private S3.
 * Runs as a one-time test instead of the continuous recorder. If the public stream
 * is unavailable, wait for it; never upload a blank or partial MP4.
 * Signed media URLs and S3 credentials are never logged.
 */
import { spawn } from 'node:child_process';
import { setTimeout as delay } from 'node:timers/promises';
import { PutObjectCommand, HeadObjectCommand, DeleteObjectCommand } from '@aws-sdk/client-s3';
import { Upload } from '@aws-sdk/lib-storage';
import { findPublicMedia, hlsFfmpegArgs } from './public-hls.mjs';

const ACCOUNT = '_frankie_rivers';
const MARKER = 'status/one-shot-_frankie_rivers-20261009.json';
const INTERVAL_MS = 60_000;

export function frankieRiversThirtySecondArgs(urls) {
  const args = hlsFfmpegArgs(urls);
  const output = args.indexOf('-movflags');
  if (output < 0) throw new Error('FFmpeg MP4 output missing');
  args.splice(output, 0, '-t', '30');
  return args;
}

export async function saveFrankieRiversClip({ s3, bucket, urls, spawnProcess = spawn }) {
  const filename = new Date().toISOString().replace(/[:.]/g, '-') + '__frankie_rivers.mp4';
  const key = 'recordings/_frankie_rivers/' + filename;
  const proc = spawnProcess('ffmpeg', frankieRiversThirtySecondArgs(urls), {
    stdio: ['ignore', 'pipe', 'ignore'],
  });
  const exit = new Promise((resolve, reject) => {
    proc.once('error', reject);
    proc.once('exit', (code, signal) => resolve({ code, signal }));
  });
  // An explicit timeout avoids leaving FFmpeg running on stalled HLS.
  const timeout = setTimeout(() => proc.kill('SIGKILL'), 90_000);
  const uploader = new Upload({
    client: s3,
    params: {
      Bucket: bucket, Key: key, Body: proc.stdout,
      ContentType: 'video/mp4', CacheControl: 'private, no-store',
    },
    queueSize: 2, partSize: 8 * 1024 * 1024, leavePartsOnError: false,
  });
  const uploaded = uploader.done().then(() => true, () => false);
  let verified = false;
  try {
    const result = await exit;
    if (result.code !== 0 || !(await uploaded)) {
      throw new Error('FFmpeg capture or S3 upload failed');
    }
    const head = await s3.send(new HeadObjectCommand({ Bucket: bucket, Key: key }));
    if (!head.ContentLength || head.ContentLength < 100_000) {
      throw new Error('Private S3 MP4 was too small');
    }
    // Verify the actual private MP4 object before marking the one-shot done.
    await s3.send(new PutObjectCommand({
      Bucket: bucket, Key: MARKER,
      Body: JSON.stringify({
        account: ACCOUNT, key, bytes: head.ContentLength,
        recorded_at: new Date().toISOString(), requested_seconds: 30,
      }),
      ContentType: 'application/json', CacheControl: 'no-store',
    }));
    verified = true;
    console.log(ACCOUNT, 'ONE_SHOT_SAVED_S3', filename, head.ContentLength, 'bytes');
    return { key, bytes: head.ContentLength };
  } finally {
    clearTimeout(timeout);
    if (proc.exitCode === null) proc.kill('SIGKILL');
    if (!verified) {
      await s3.send(new DeleteObjectCommand({ Bucket: bucket, Key: key })).catch(() => {});
    }
  }
}

async function reportFrankieStatus({ s3, bucket, state, message, lastSaved = null }) {
  try {
    await s3.send(new PutObjectCommand({
      Bucket: bucket, Key: 'status/' + ACCOUNT + '.json',
      Body: JSON.stringify({
        account: ACCOUNT, status: state, message,
        checked_at: new Date().toISOString(), last_saved: lastSaved,
      }),
      ContentType: 'application/json', CacheControl: 'no-store',
    }));
  } catch { console.error(ACCOUNT, 'test status update failed'); }
}

export async function runFrankieRiversOneShot({
  s3, bucket, signal, discover = findPublicMedia, sleep = delay,
  saveClip = saveFrankieRiversClip, intervalMs = INTERVAL_MS, report = reportFrankieStatus,
}) {
  if (!signal) throw new Error('Abort signal required');
  try {
    await s3.send(new HeadObjectCommand({ Bucket: bucket, Key: MARKER }));
    console.log(ACCOUNT, 'one-shot already saved in private S3; no duplicate');
    await report({ s3, bucket, state: 'offline', message: '30 seconden proefopname al eerder opgeslagen' });
    return { kind: 'already-saved' };
  } catch (error) {
    if (error?.$metadata?.httpStatusCode !== 404 &&
        error?.name !== 'NotFound' && error?.name !== 'NoSuchKey') {
      throw new Error('Private S3 marker check failed');
    }
  }
  console.log(ACCOUNT, '30-second private one-shot waiting for playable public livestream');
  await report({ s3, bucket, state: 'unknown', message: 'Wachten op livestream voor 30 seconden testopname' });
  const aborted = new Promise(resolve => {
    if (signal.aborted) resolve();
    else signal.addEventListener('abort', resolve, { once: true });
  });
  while (!signal.aborted) {
    try {
      const source = await discover(ACCOUNT);
      if (signal.aborted) break;
      if (source?.kind === 'live' && Array.isArray(source.urls) && source.urls.length >= 2) {
        console.log(ACCOUNT, 'one-shot video+audio detected; recording 30 seconds');
        await report({ s3, bucket, state: 'recording', message: 'Eenmalige proefopname van 30 seconden bezig' });
        const saved = await saveClip({ s3, bucket, urls: source.urls });
        await report({ s3, bucket, state: 'offline', message: '30 seconden MP4 privé opgeslagen', lastSaved: saved.key.split('/').at(-1) });
        return { kind: 'saved', ...saved };
      }
    } catch {
      // No signed URLs, credentials, or upstream error bodies in logs.
      console.error(ACCOUNT, 'one-shot source or capture unavailable; retrying');
      await report({ s3, bucket, state: 'error', message: 'Testopname mislukt; nieuwe poging over een minuut' });
    }
    if (signal.aborted) break;
    await Promise.race([
      sleep(intervalMs),
      aborted,
    ]);
  }
  return { kind: 'stopped' };
}
