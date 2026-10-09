/**
 * Continuous HLS-only recorder for the owner's additional rooms.
 * Shares the existing Railway recorder's private S3 connection but does not
 * share its browser, Xvfb display or PulseAudio sink.
 * Never log signed HLS URLs, FFmpeg arguments, cookies or credentials.
 */
import { spawn } from 'node:child_process';
import { setTimeout as delay } from 'node:timers/promises';
import { PutObjectCommand, HeadObjectCommand, DeleteObjectCommand } from '@aws-sdk/client-s3';
import { Upload } from '@aws-sdk/lib-storage';
import { findPublicMedia, hlsFfmpegArgs } from './public-hls.mjs';
import { nextPollDelay } from './schedule.mjs';

const ALLOWED_EXTRA_ACCOUNTS = new Set(['lucycums', 'leo_kitty', 'mon1_day', 'cutefacebigass', 'ricasashaa']);
const POLL_MS = 60_000;

export function extraAccountFor(primary) {
  return primary === 'knock1knock' ? 'lucycums'
    : primary === 'emyii' ? 'leo_kitty' : null;
}

export function isPlayable(source) {
  return source?.kind === 'live' && Array.isArray(source.urls) && source.urls.length >= 2;
}

export function hlsContinuousArgs(urls) {
  const args = hlsFfmpegArgs(urls);
  // This is a full-live recording, not the earlier one-time 30-second test.
  if (args.includes('-t')) throw new Error('Continuous recorder must not have a time limit');
  return args;
}

export async function reportHlsStatus({ s3, bucket, account, state, message, lastSaved = null }) {
  const checked_at = new Date().toISOString();
  await s3.send(new PutObjectCommand({
    Bucket: bucket, Key: 'status/' + account + '.json',
    Body: JSON.stringify({ account, status: state, message, checked_at, last_saved: lastSaved }),
    ContentType: 'application/json', CacheControl: 'no-store',
  }));
  console.log(checked_at, account, state, message);
}

async function bounded(promise, ms, onTimeout) {
  let timer;
  try {
    return await Promise.race([
      promise,
      new Promise((_, reject) => {
        timer = setTimeout(() => {
          onTimeout?.();
          reject(new Error('Recorder operation timed out'));
        }, ms);
      }),
    ]);
  } finally {
    clearTimeout(timer);
  }
}

export async function startHlsCapture({ s3, bucket, account, urls }) {
  if (!ALLOWED_EXTRA_ACCOUNTS.has(account)) throw new Error('Unexpected extra account');
  const filename = new Date().toISOString().replace(/[:.]/g, '-') + '_' + account + '.mp4';
  const key = 'recordings/' + account + '/' + filename;
  // Never print the signed URLs embedded in these process arguments.
  const proc = spawn('ffmpeg', hlsContinuousArgs(urls), { stdio: ['ignore', 'pipe', 'ignore'] });
  let exitInfo = null;
  const exited = new Promise((resolve, reject) => {
    proc.once('error', reject);
    proc.once('exit', (code, signal) => {
      exitInfo = { code, signal };
      resolve(exitInfo);
    });
  });
  // Ensure errors are always observed, even if the process fails before stop().
  void exited.catch(() => {});
  const upload = new Upload({
    client: s3,
    params: {
      Bucket: bucket, Key: key, Body: proc.stdout,
      ContentType: 'video/mp4', CacheControl: 'private, no-store',
    },
    queueSize: 2, partSize: 8 * 1024 * 1024, leavePartsOnError: false,
  });
  const uploaded = upload.done().then(
    () => ({ ok: true }), () => ({ ok: false }),
  );
  let finished = false;
  return {
    key,
    async check() {
      const result = await Promise.race([uploaded, delay(15).then(() => null)]);
      if (result && !result.ok) throw new Error('Private S3 upload interrupted');
      return exitInfo !== null || proc.exitCode !== null;
    },
    async stop() {
      if (finished) return key;
      finished = true;
      if (!exitInfo && proc.exitCode === null) proc.kill('SIGINT');
      try {
        // On a stopped stream, FFmpeg must finalize the MP4 and close stdout.
        const exit = await bounded(exited, 30_000, () => proc.kill('SIGKILL'));
        const result = await bounded(uploaded, 30_000);
        if (!result.ok) throw new Error('Private S3 upload failed');
        if (exit.code !== 0 && exit.code !== 255 && exit.signal !== 'SIGINT') {
          throw new Error('FFmpeg could not finalize MP4');
        }
        const head = await s3.send(new HeadObjectCommand({ Bucket: bucket, Key: key }));
        if (!head.ContentLength || head.ContentLength < 100_000) {
          throw new Error('Recorded MP4 was too small');
        }
        console.log(new Date().toISOString(), account, 'S3_MP4_SAVED', filename, head.ContentLength, 'bytes');
        return key;
      } catch {
        await s3.send(new DeleteObjectCommand({ Bucket: bucket, Key: key })).catch(() => {});
        throw new Error('Recording was not verified in private S3');
      }
    },
  };
}

/**
 * Monitor each additional owner-controlled live room every minute.
 * Starts when public video+audio become playable and finishes on stream end,
 * with tolerance for brief missing status checks.
 */
export async function runHlsMonitor({
  s3, bucket, account = 'lucycums', signal,
  discover = findPublicMedia, startCapture = startHlsCapture,
  report = reportHlsStatus, sleep = delay, intervalMs = POLL_MS,
}) {
  if (!ALLOWED_EXTRA_ACCOUNTS.has(account)) throw new Error('Unexpected extra account');
  if (!signal) throw new Error('Abort signal required');
  let recording = null;
  let missing = 0;
  let lastSaved = null;
  const abort = new Promise(resolve => {
    if (signal.aborted) resolve();
    else signal.addEventListener('abort', resolve, { once: true });
  });
  const publish = async (state, message) => {
    try { await report({ s3, bucket, account, state, message, lastSaved }); }
    catch { console.error(account, 'status update failed; retrying next poll'); }
  };

  await publish('unknown', 'Automatische recorder gestart; controle elke minuut');
  while (!signal.aborted) {
    const pollStarted = Date.now();
    try {
      const source = await discover(account);
      if (signal.aborted) break;
      const live = isPlayable(source);
      if (recording && await recording.check()) {
        const done = recording;
        recording = null;
        await publish('uploading', 'Livestream beëindigd; MP4 wordt gecontroleerd');
        const key = await done.stop();
        console.log(account, 'completed', key.split('/').at(-1));
        lastSaved = key.split('/').at(-1);
        missing = 0;
        await publish('unknown', 'Opname opgeslagen; wachten op volgende live');
      } else if (!recording && live) {
        recording = await startCapture({ s3, bucket, account, urls: source.urls });
        missing = 0;
        await publish('recording', 'Live gedetecteerd: beeld en geluid worden automatisch opgenomen');
      } else if (recording) {
        missing = live ? 0 : missing + 1;
        if (missing >= 2) {
          const done = recording;
          recording = null;
          await publish('uploading', 'Stream niet meer beschikbaar; MP4 wordt afgerond');
          const key = await done.stop();
          console.log(account, 'completed', key.split('/').at(-1));
          lastSaved = key.split('/').at(-1);
          missing = 0;
          await publish('offline', 'Opname opgeslagen; wachten op volgende live');
        } else {
          await publish('recording', live
            ? 'Livestream wordt automatisch opgenomen'
            : 'Opname loopt; tijdelijke onderbreking van de statuscontrole');
        }
      } else {
        // An inaccessible public HLS source does not prove the broadcaster is offline.
        const offline = source?.kind === 'offline';
        await publish(offline ? 'offline' : 'unknown',
          offline ? 'Stream is offline; automatisch wachten op volgende live'
            : 'Geen afspeelbare publieke video en audio; volgende controle over een minuut');
      }
    } catch {
      console.error(account, 'capture or status check failed; retrying');
      if (recording) {
        const failed = recording;
        recording = null;
        try { await failed.stop(); } catch { /* Invalid MP4 already removed. */ }
      }
      missing = 0;
      await publish('error', 'Opnamecontrole mislukt; automatische nieuwe poging over een minuut');
    }
    if (!signal.aborted) {
      await Promise.race([
        sleep(nextPollDelay(pollStarted, Date.now(), intervalMs)),
        abort,
      ]);
    }
  }
  if (recording) {
    await publish('uploading', 'Recorder wordt herstart; huidige MP4 wordt afgerond');
    try {
      const key = await recording.stop();
      console.log(account, 'completed on shutdown', key.split('/').at(-1));
      lastSaved = key.split('/').at(-1);
      await publish('offline', 'Opname opgeslagen; recorder herstart');
    } catch {
      await publish('error', 'Huidige MP4 kon niet worden afgerond tijdens herstart');
    }
  }
}
