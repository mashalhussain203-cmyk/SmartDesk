/**
 * SmartDesk private live recorder - Chrome + FFmpeg + Railway bucket.
 * One instance per account. No Chaturbate API or access-control bypass.
 */
import { chromium } from 'playwright-core';
import { S3Client, PutObjectCommand } from '@aws-sdk/client-s3';
import { Upload } from '@aws-sdk/lib-storage';
import { spawn } from 'node:child_process';
import { mkdir } from 'node:fs/promises';
import { resolve } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

const account = process.env.LIVE_ACCOUNT || 'knock1knock';
if (!['knock1knock', 'emyii'].includes(account)) throw new Error('Invalid account');
const env = process.env;
for (const key of ['LIVE_S3_ENDPOINT', 'LIVE_S3_BUCKET', 'LIVE_S3_REGION', 'LIVE_S3_ACCESS_KEY_ID', 'LIVE_S3_SECRET_ACCESS_KEY', 'DISPLAY', 'PULSE_SOURCE']) {
  if (!env[key]) throw new Error('Missing required configuration: ' + key);
}
if (!env.LIVE_S3_ENDPOINT.startsWith('https://')) throw new Error('Private bucket must use HTTPS');
const bucket = env.LIVE_S3_BUCKET;
const s3 = new S3Client({
  endpoint: env.LIVE_S3_ENDPOINT, region: env.LIVE_S3_REGION, forcePathStyle: true, maxAttempts: 5,
  credentials: { accessKeyId: env.LIVE_S3_ACCESS_KEY_ID, secretAccessKey: env.LIVE_S3_SECRET_ACCESS_KEY },
});
const profile = resolve(env.LIVE_PROFILE_ROOT || '/tmp/live-chrome-profiles', account);
const display = env.DISPLAY;
const audio = env.PULSE_SOURCE;
const executablePath = env.CHROME_PATH || '/usr/bin/chromium';
const interval = 60000, width = 1280, height = 720;
const maxSeconds = Math.max(0, Number(env.LIVE_TEST_SECONDS || 0));
let exiting = false, recording = null, browser, interrupt;
const interrupted = new Promise(resolve => { interrupt = resolve; });
const pause = ms => Promise.race([delay(ms), interrupted]);
await mkdir(profile, { recursive: true, mode: 0o700 });

async function update(status, message) {
  const checked_at = new Date().toISOString();
  await s3.send(new PutObjectCommand({
    Bucket: bucket, Key: 'status/' + account + '.json',
    Body: JSON.stringify({ account, status, message, checked_at }),
    ContentType: 'application/json', CacheControl: 'no-store',
  }));
  console.log(checked_at, account, status, message);
}
function shell(name, args, capture = false) {
  const proc = spawn(name, args, { stdio: ['ignore', capture ? 'pipe' : 'ignore', 'pipe'] });
  proc.stderr?.on('data', chunk => console.error(name + ': ' + String(chunk).slice(0, 800)));
  return proc;
}
function untilExit(proc) {
  return new Promise((done, fail) => {
    proc.once('error', fail);
    proc.once('exit', (code, signal) => done({ code, signal }));
  });
}
async function capture() {
  const filename = new Date().toISOString().replace(/[:.]/g, '-') + '_' + account + '.mp4';
  const key = 'recordings/' + account + '/' + filename;
  const args = [
    '-hide_banner', '-loglevel', 'warning', '-nostdin',
    '-f', 'x11grab', '-framerate', '20', '-video_size', width + 'x' + height, '-i', display + '.0+0,0',
    '-f', 'pulse', '-i', audio, '-map', '0:v:0', '-map', '1:a:0',
    '-c:v', 'libx264', '-preset', 'ultrafast', '-crf', '27', '-pix_fmt', 'yuv420p', '-g', '40',
    '-c:a', 'aac', '-b:a', '128k',
    '-movflags', '+frag_keyframe+empty_moov+default_base_moof', '-f', 'mp4', 'pipe:1',
  ];
  const proc = shell('ffmpeg', args, true);
  const exited = untilExit(proc);
  const upload = new Upload({
    client: s3, params: { Bucket: bucket, Key: key, Body: proc.stdout, ContentType: 'video/mp4', CacheControl: 'private, no-store' },
    queueSize: 2, partSize: 8 * 1024 * 1024, leavePartsOnError: false,
  });
  const uploaded = upload.done().then(() => ({ ok: true }), error => ({ ok: false, error }));
  await delay(1500);
  if (proc.exitCode !== null) {
    const result = await uploaded;
    throw new Error('FFmpeg startup failed: ' + (result.error?.message || proc.exitCode));
  }
  let stopped = false;
  return {
    started: Date.now(),
    async check() {
      const result = await Promise.race([uploaded, delay(30).then(() => null)]);
      if (result && !result.ok) throw new Error('Upload failed: ' + result.error?.message);
      if (proc.exitCode !== null && proc.exitCode !== 255) throw new Error('FFmpeg stopped unexpectedly');
    },
    async stop() {
      if (stopped) return key;
      stopped = true;
      if (proc.exitCode === null) proc.kill('SIGINT');
      const exit = await exited;
      const result = await uploaded;
      if (!result.ok) throw new Error('MP4 upload failed: ' + result.error?.message);
      if (![0, 255].includes(exit.code) && exit.signal !== 'SIGINT') throw new Error('FFmpeg failed to finalize');
      return key;
    },
  };
}

async function detect(page) {
  const content = (await page.locator('body').innerText({ timeout: 12000 }).catch(() => '')).slice(0, 9000);
  if (/verify.{0,35}age|age verification|confirm.{0,35}18|you must be 18|log in to continue/i.test(content)) {
    return { kind: 'needs_setup', detail: 'Leeftijdsbevestiging of login nodig in de Chrome-browser' };
  }
  // Only detect playing video; a LIVE label alone cannot prove usable recording.
  let playing = false;
  for (const frame of page.frames()) {
    const ok = await frame.evaluate(async () => {
      const videos = [...document.querySelectorAll('video')];
      const states = await Promise.all(videos.map(async v => {
        if (v.paused || v.ended || v.readyState < 2) return false;
        const t = v.currentTime;
        await new Promise(r => setTimeout(r, 1200));
        return !v.paused && v.readyState >= 2 && v.currentTime > t;
      }));
      return states.some(Boolean);
    }).catch(() => false);
    if (ok) { playing = true; break; }
  }
  if (playing) return { kind: 'live', detail: 'Chrome speelt videobeelden af' };
  if (/this room is (currently )?offline|user is currently offline|is offline|room is offline/i.test(content)) {
    return { kind: 'offline', detail: 'De pagina toont offline' };
  }
  return { kind: 'unknown', detail: 'Geen afspelende video gevonden; pagina of leeftijdscontrole nakijken' };
}

async function main() {
  await update('unknown', 'Chrome-recorder wordt gestart');
  browser = await chromium.launchPersistentContext(profile, {
    executablePath, headless: false,
    viewport: { width, height },
    args: ['--window-position=0,0', '--window-size=1280,720', '--autoplay-policy=no-user-gesture-required'],
  });
  const page = browser.pages()[0] || await browser.newPage();
  let offlineCount = 0;
  let playbackMissingCount = 0;

  while (!exiting) {
    try {
      if (!recording) {
        await page.goto('https://chaturbate.com/' + account + '/', { waitUntil: 'domcontentloaded', timeout: 30000 });
        await pause(7000);
      }
      const result = await detect(page);

      if (recording) await recording.check();
      if (!recording && result.kind === 'live') {
        recording = await capture();
        offlineCount = 0;
        playbackMissingCount = 0;
        await update('recording', 'Opname gestart; wacht op het einde van de livestream');
      } else if (recording) {
        if (result.kind === 'offline') offlineCount += 1;
        else if (result.kind === 'live') offlineCount = 0;
        if (result.kind === 'unknown') playbackMissingCount += 1;
        else if (result.kind === 'live') playbackMissingCount = 0;
        // Two explicit OFFLINE polls or three consecutive missed playback checks
        // end the recording instead of allowing endless blank capture.
        if (offlineCount >= 2 || playbackMissingCount >= 3 || (maxSeconds > 0 && Date.now() - recording.started >= maxSeconds * 1000)) {
          const done = recording;
          recording = null;
          await update('live', 'MP4 wordt afgerond');
          const key = await done.stop();
          await update(result.kind === 'offline' ? 'offline' : 'live', 'Opname opgeslagen: ' + key.split('/').at(-1));
          offlineCount = 0;
          playbackMissingCount = 0;
          if (maxSeconds > 0) exiting = true; // One test clip only.
        } else {
          await update('recording', result.kind === 'live' ? 'Livestream wordt opgenomen' : 'Opname loopt; status niet zeker (' + result.kind + ')');
        }
      } else {
        await update(result.kind, result.detail);
      }
    } catch (err) {
      await update('error', String(err?.message || err).slice(0, 150));
      // Do not discard an ongoing recording due to temporary browser failures.
    }
    if (!exiting) {
      const remaining = recording && maxSeconds > 0 ? maxSeconds * 1000 - (Date.now() - recording.started) : interval;
      await pause(Math.max(1000, Math.min(interval, remaining)));
    }
  }
  if (recording) await recording.stop();
  await browser.close();
}

process.on('SIGTERM', () => { exiting = true; interrupt(); });
process.on('SIGINT', () => { exiting = true; interrupt(); });
try {
  await main();
} catch (error) {
  await update('error', String(error?.message || error).slice(0, 150));
  process.exitCode = 1;
}
