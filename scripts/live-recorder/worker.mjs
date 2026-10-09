/**
 * SmartDesk private live recorder - Chrome + FFmpeg + Railway bucket.
 * One instance per account. No Chaturbate API or access-control bypass.
 */
import { chromium } from 'playwright-core';
import { S3Client, PutObjectCommand, HeadObjectCommand } from '@aws-sdk/client-s3';
import { Upload } from '@aws-sdk/lib-storage';
import { spawn } from 'node:child_process';
import { mkdir } from 'node:fs/promises';
import { resolve } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';
import { nextPollDelay } from './schedule.mjs';
import { acceptAdultTerms, isAdultTermsScreen } from './age-consent.mjs';
import { newNetworkSummary, recordHttpResponse, diagnoseAccess } from './access-diagnostics.mjs';
import { findPublicMedia, hlsFfmpegArgs } from './public-hls.mjs';

const account = process.env.LIVE_ACCOUNT || 'knock1knock';
if (!['knock1knock', 'emyii', 'lucycums'].includes(account)) throw new Error('Invalid account');
const env = process.env;
for (const key of ['LIVE_S3_ENDPOINT', 'LIVE_S3_BUCKET', 'LIVE_S3_REGION', 'LIVE_S3_ACCESS_KEY_ID', 'LIVE_S3_SECRET_ACCESS_KEY', 'DISPLAY', 'PULSE_SOURCE']) {
  if (!env[key]) throw new Error('Missing required configuration: ' + key);
}
if (!env.LIVE_S3_ENDPOINT.startsWith('https://')) throw new Error('Private bucket must use HTTPS');
const bucket = env.LIVE_S3_BUCKET;
const s3 = new S3Client({
  endpoint: env.LIVE_S3_ENDPOINT, region: env.LIVE_S3_REGION,
  forcePathStyle: env.LIVE_S3_URL_STYLE !== 'virtual-host', maxAttempts: 5,
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
function shell(name, args, capture = false, privateMediaUrls = false) {
  const proc = spawn(name, args, { stdio: ['ignore', capture ? 'pipe' : 'ignore', 'pipe'] });
  proc.stderr?.on('data', chunk => {
    if (privateMediaUrls) {
      // FFmpeg diagnostics can contain signed HLS URLs: never print raw stderr.
      const message = String(chunk);
      console.error(name + ': ' + (/403|Forbidden/i.test(message) ? 'HTTP 403' :
        /404|Not Found/i.test(message) ? 'HTTP 404' : 'HLS media processing warning'));
    } else console.error(name + ': ' + String(chunk).slice(0, 800));
  });
  return proc;
}
function untilExit(proc) {
  return new Promise((done, fail) => {
    proc.once('error', fail);
    proc.once('exit', (code, signal) => done({ code, signal }));
  });
}
async function capture(mediaUrls = null) {
  const filename = new Date().toISOString().replace(/[:.]/g, '-') + '_' + account + '.mp4';
  const key = 'recordings/' + account + '/' + filename;
  const args = mediaUrls ? hlsFfmpegArgs(mediaUrls) : [
    '-hide_banner', '-loglevel', 'warning', '-nostdin',
    '-f', 'x11grab', '-framerate', '20', '-video_size', width + 'x' + height, '-i', display + '.0+0,0',
    '-f', 'pulse', '-i', audio, '-map', '0:v:0', '-map', '1:a:0',
    '-c:v', 'libx264', '-preset', 'ultrafast', '-crf', '27', '-pix_fmt', 'yuv420p', '-g', '40',
    '-c:a', 'aac', '-b:a', '128k',
    '-movflags', '+frag_keyframe+empty_moov+default_base_moof', '-f', 'mp4', 'pipe:1',
  ];
  const proc = shell('ffmpeg', args, true, Boolean(mediaUrls));
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
    mode: mediaUrls ? 'hls' : 'chrome',
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
      // A completed multipart upload must also exist as a nonempty private object.
      const head = await s3.send(new HeadObjectCommand({ Bucket: bucket, Key: key }));
      if (!head.ContentLength || head.ContentLength < 100_000) {
        throw new Error('MP4 upload exists but is unexpectedly small');
      }
      console.log(new Date().toISOString(), account, 'saved', 'Private S3 MP4 verified:', head.ContentLength, 'bytes');
      return key;
    },
  };
}

async function detect(page) {
  const content = (await page.locator('body').innerText({ timeout: 12000 }).catch(() => '')).slice(0, 9000);
  if (isAdultTermsScreen(content) || /verify.{0,35}age|age verification|confirm.{0,35}18|you must be 18|log in to continue/i.test(content)) {
    return { kind: 'needs_setup', detail: 'Leeftijdsbevestiging of login nodig in de Chrome-browser' };
  }
  // Only detect playing video; a LIVE label alone cannot prove usable recording.
  let playing = false;
  for (const frame of page.frames()) {
    const ok = await frame.evaluate(async () => {
      const videos = [...document.querySelectorAll('video')];
      const states = await Promise.all(videos.map(async v => {
        // Normal browser autoplay attempt; never bypass an age/login gate.
        if (v.paused && !v.ended && (v.currentSrc || v.srcObject)) {
          await Promise.race([
            v.play().catch(() => {}),
            new Promise(r => setTimeout(r, 1500)),
          ]);
        }
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
  // Safe diagnostics only: counts and playback state, never page text or images.
  const counts = { total: 0, visible: 0, paused: 0, ready: 0, withSource: 0, mediaErrors: 0 };
  for (const frame of page.frames()) {
    const info = await frame.evaluate(() => {
      const videos = Array.from(document.querySelectorAll('video'));
      return {
        total: videos.length,
        visible: videos.filter(v => v.getBoundingClientRect().width >= 240 && v.getBoundingClientRect().height >= 135).length,
        paused: videos.filter(v => v.paused).length,
        ready: videos.filter(v => v.readyState >= 2).length,
        withSource: videos.filter(v => !!(v.currentSrc || v.srcObject)).length,
        mediaErrors: videos.filter(v => !!v.error).length,
      };
    }).catch(() => null);
    if (info) for (const key of Object.keys(counts)) counts[key] += info[key] || 0;
  }
  return {
    kind: 'unknown',
    detail: 'Geen afspelende video (videospelers: ' + counts.total + ', zichtbaar: ' + counts.visible +
      ', gepauzeerd: ' + counts.paused + ', geladen: ' + counts.ready +
      ', mediabron: ' + counts.withSource + ', mediafouten: ' + counts.mediaErrors + ')',
  };
}

async function main() {
  await update('unknown', 'Chrome-recorder wordt gestart');
  browser = await chromium.launchPersistentContext(profile, {
    executablePath, headless: false,
    viewport: { width, height },
    args: ['--window-position=0,0', '--window-size=1280,720', '--autoplay-policy=no-user-gesture-required'],
  });
  const page = browser.pages()[0] || await browser.newPage();
  // Safe HTTP diagnostics: count only response statuses and resource types.
  // Do not log URLs, cookies, response bodies, or stream addresses.
  let network = newNetworkSummary();
  page.on('response', response => {
    try {
      const request = response.request();
      const isMain = request.isNavigationRequest() && request.frame() === page.mainFrame();
      const hostname = new URL(response.url()).hostname.toLowerCase();
      const origin = hostname === 'chaturbate.com' || hostname.endsWith('.chaturbate.com')
        ? 'site' : hostname === 'highwebmedia.com' || hostname.endsWith('.highwebmedia.com')
          ? 'media-network' : 'other';
      recordHttpResponse(network, response.status(), request.resourceType(), isMain, origin);
    } catch { /* Browser may detach a frame while a response arrives. */ }
  });
  page.on('pageerror', () => { network.pageScriptErrors++; });
  page.on('requestfailed', request => {
    if (['document', 'media', 'xhr', 'fetch', 'script'].includes(request.resourceType())) {
      network.failedRequests++;
    }
  });
  let offlineCount = 0;
  let playbackMissingCount = 0;

  while (!exiting) {
    const pollStartedAt = Date.now();
    try {
      let ageConsent = { detected: false, clicked: false };
      if (!recording) {
        network = newNetworkSummary();
        const response = await page.goto('https://chaturbate.com/' + account + '/', {
          waitUntil: 'domcontentloaded', timeout: 30000,
        });
        // A navigation response can arrive before the event handler attaches to the frame.
        if (network.mainHttpStatus === null && response) {
          recordHttpResponse(network, response.status(), 'document', true);
        }
        await pause(7000);
        // User expressly confirmed they are 18+ and agreed to these terms.
        // Click only the real Chaturbate age/terms modal, never other gates.
        ageConsent = await acceptAdultTerms(page, env.LIVE_ACCEPT_ADULT_TERMS === '1');
        if (ageConsent.clicked) {
          await update('unknown', '18+-voorwaarden bevestigd; videospeler opnieuw laden');
          await pause(5000);
        }
      }
      const playback = ageConsent.detected && !ageConsent.clicked
        ? { kind: 'needs_setup', detail: '18+-voorwaarden zichtbaar maar niet bevestigd' }
        : await detect(page);
      let result = diagnoseAccess(playback, network);
      let publicMediaUrls = null;
      // Chrome can show an empty player even while a public broadcast is live.
      // Try the actual public HLS source without an API token or bypassing
      // a visible age/login requirement. This never logs signed stream URLs.
      if (env.LIVE_USE_HLS !== '0' && playback.kind !== 'needs_setup' &&
          (playback.kind !== 'live' || recording?.mode === 'hls')) {
        const hls = await findPublicMedia(account);
        if (hls.kind === 'live') {
          publicMediaUrls = hls.urls;
          result = { kind: 'live', detail: hls.detail };
        } else if (result.kind === 'unknown') {
          result = { ...result, detail: result.detail + '; ' + hls.detail };
        }
      }

      if (recording) await recording.check();
      if (!recording && result.kind === 'live') {
        recording = await capture(publicMediaUrls);
        offlineCount = 0;
        playbackMissingCount = 0;
        await update('recording', 'Opname gestart via ' + recording.mode + '; wacht op het einde van de livestream');
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
      // Poll once per minute from poll START, including page navigation,
      // player checks, and status upload. Do not add 60 seconds afterward.
      const nextCheckMs = nextPollDelay(pollStartedAt, Date.now(), interval);
      const testRemainingMs = recording && maxSeconds > 0
        ? maxSeconds * 1000 - (Date.now() - recording.started)
        : Infinity;
      await pause(Math.max(1000, Math.min(nextCheckMs, testRemainingMs)));
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
