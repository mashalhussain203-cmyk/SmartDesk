/**
 * SmartDesk local Chrome recorder — no Chaturbate API.
 *
 * Runs as a separate persistent process on the same host/volume as Laravel.
 * A headful Chrome browser must have a private X11 display; ffmpeg captures
 * that display and its PulseAudio monitor source. This does NOT defeat login,
 * consent gates, DRM or stream permissions.
 *
 * One worker + one X11 display per channel. Example:
 * LIVE_ACCOUNT=knock1knock DISPLAY=:99 PULSE_SOURCE=monitor-source node scripts/live-recorder/worker.mjs
 */
import { chromium } from 'playwright-core';
import { spawn } from 'node:child_process';
import { mkdir, writeFile, rename, unlink, stat } from 'node:fs/promises';
import { join, resolve } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

const account = process.env.LIVE_ACCOUNT || 'knock1knock';
if (!['knock1knock', 'emyii'].includes(account)) throw new Error('Invalid LIVE_ACCOUNT');

const root = resolve(process.env.LIVE_STORAGE_ROOT || 'storage/app/private/live-recordings', account);
const profile = resolve(process.env.LIVE_PROFILE_ROOT || 'storage/app/private/live-chrome-profiles', account);
const display = process.env.DISPLAY;
const audio = process.env.PULSE_SOURCE;
const executablePath = process.env.CHROME_PATH || '/usr/bin/chromium';
const interval = 60000;
const width = 1280;
const height = 720;
const maxSeconds = Math.max(0, Number(process.env.LIVE_TEST_SECONDS || 0));
let exiting = false;
let recording = null;
let browser;

if (!display || !/^:[0-9]+$/.test(display)) throw new Error('Set DISPLAY to a dedicated X11 display such as :99');
if (!audio) throw new Error('Set PULSE_SOURCE to your display audio monitor device (for sound)');
await mkdir(root, { recursive: true, mode: 0o700 });
await mkdir(profile, { recursive: true, mode: 0o700 });

const statusPath = join(root, 'status.json');
async function update(status, message) {
  const checked_at = new Date().toISOString();
  const tmp = join(root, 'status.json.tmp');
  await writeFile(tmp, JSON.stringify({ account, status, message, checked_at }), { mode: 0o600 });
  await rename(tmp, statusPath);
  console.log(checked_at, account, status, message);
}
function shell(name, args) {
  const proc = spawn(name, args, { stdio: ['pipe', 'ignore', 'pipe'] });
  proc.stderr?.on('data', chunk => console.error(String(chunk).slice(0, 800)));
  return proc;
}
function untilExit(proc) {
  return new Promise((done, fail) => {
    proc.once('error', fail);
    proc.once('exit', (code, signal) => done({ code, signal }));
  });
}
async function capture() {
  const now = new Date().toISOString().replace(/[:.]/g, '-');
  const basename = now + '_' + account;
  const raw = join(root, basename + '.mkv');
  const tmp = join(root, basename + '.temporary.mp4');
  const final = join(root, basename + '.mp4');

  const args = [
    '-nostdin', '-hide_banner', '-loglevel', 'warning', '-y',
    '-f', 'x11grab', '-framerate', '20', '-video_size', width + 'x' + height,
    '-i', display + '.0+0,0',
    '-f', 'pulse', '-i', audio,
    '-map', '0:v:0', '-map', '1:a:0',
    '-c:v', 'libx264', '-preset', 'ultrafast', '-crf', '26', '-pix_fmt', 'yuv420p',
    '-c:a', 'aac', '-b:a', '128k', raw,
  ];
  // -nostdin prevents graceful q; use SIGINT and wait for ffmpeg to close Matroska.
  const proc = shell('ffmpeg', args);
  const exited = untilExit(proc);
  await delay(1200);
  if (proc.exitCode !== null) throw new Error('FFmpeg could not start; check X11 display and PulseAudio');
  let stopped = false;

  return {
    started: Date.now(),
    async stop() {
      if (stopped) return;
      stopped = true;
      proc.kill('SIGINT');
      const end = await exited;
      if (end.code !== 0 && end.signal !== 'SIGINT' && end.code !== 255) {
        console.error('Recording ffmpeg exit:', end);
      }
      try {
        const stats = await stat(raw);
        if (stats.size < 1024) throw new Error('Recorded file is empty');
        const convert = shell('ffmpeg', ['-nostdin', '-hide_banner', '-loglevel', 'error', '-y', '-i', raw, '-c', 'copy', '-movflags', '+faststart', tmp]);
        const result = await untilExit(convert);
        if (result.code !== 0) throw new Error('MP4 remux failed');
        await rename(tmp, final);
        await unlink(raw);
        return final;
      } catch (error) {
        await update('error', 'Opslaan naar MP4 mislukt: ' + error.message);
        throw error;
      }
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

  while (!exiting) {
    try {
      if (!recording) {
        await page.goto('https://chaturbate.com/' + account + '/', { waitUntil: 'domcontentloaded', timeout: 30000 });
        await delay(7000);
      }
      const result = await detect(page);

      if (!recording && result.kind === 'live') {
        recording = await capture();
        offlineCount = 0;
        await update('recording', 'Opname gestart; wacht op het einde van de livestream');
      } else if (recording) {
        if (result.kind === 'offline') offlineCount += 1;
        else if (result.kind === 'live') offlineCount = 0;
        // An uncertain player/network state is not evidence that the show ended.
        if (offlineCount >= 2 || (maxSeconds > 0 && Date.now() - recording.started >= maxSeconds * 1000)) {
          const done = recording;
          recording = null;
          await update('live', 'MP4 wordt afgerond');
          const path = await done.stop();
          await update('offline', 'Opname opgeslagen: ' + path.split('/').at(-1));
          offlineCount = 0;
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
      await delay(Math.max(1000, Math.min(interval, remaining)));
    }
  }
  if (recording) await recording.stop();
  await browser.close();
}

process.on('SIGTERM', () => { exiting = true; });
process.on('SIGINT', () => { exiting = true; });
try {
  await main();
} catch (error) {
  await update('error', String(error?.message || error).slice(0, 150));
  process.exitCode = 1;
}
