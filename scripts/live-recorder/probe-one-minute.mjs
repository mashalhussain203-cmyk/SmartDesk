import { spawnSync } from 'node:child_process';
import { mkdtempSync, statSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const account = 'lucycums';
const outDir = mkdtempSync(join(tmpdir(), 'smartdesk-ffmpeg-probe-'));
const file = join(outDir, 'sample.mp4');
function allowed(url) {
  try {
    const u = new URL(url);
    const host = u.hostname.toLowerCase();
    return u.protocol === 'https:' && (
      host === 'mmcdn.com' || host.endsWith('.mmcdn.com') ||
      host === 'highwebmedia.com' || host.endsWith('.highwebmedia.com') ||
      host === 'chaturbate.com' || host.endsWith('.chaturbate.com'));
  } catch { return false; }
}
function category(error) {
  if (/403|forbidden/i.test(error)) return 'HTTP_403';
  if (/404|not found/i.test(error)) return 'HTTP_404';
  if (/timeout|timed out/i.test(error)) return 'TIMEOUT';
  if (/invalid data|invalid argument/i.test(error)) return 'INVALID_MEDIA';
  if (/input\/output|I\/O error/i.test(error)) return 'MEDIA_IO_ERROR';
  return 'OTHER_ERROR';
}
try {
  const r = spawnSync('yt-dlp', [
    '--no-config', '--no-playlist', '--no-warnings', '--no-progress', '--get-url',
    '-f', 'bv*+ba/b', '--socket-timeout', '12', '--retries', '0', '--extractor-retries', '0',
    'https://chaturbate.com/' + account + '/',
  ], { encoding: 'utf8', timeout: 40000, maxBuffer: 256 * 1024 });
  const urls = String(r.stdout || '').split(/\r?\n/).filter(s => /^https:\/\//.test(s));
  if (r.status !== 0 || urls.length < 2 || !urls.every(allowed)) {
    console.log('ONE_MINUTE_RESULT ' + JSON.stringify({
      account, status: 'SOURCE_UNAVAILABLE', urlCount: urls.length,
      errorCategory: category(String(r.stderr || '')), exitCode: r.status,
    }));
    process.exitCode = 1;
  } else {
    console.log('ONE_MINUTE_SOURCE ' + JSON.stringify({
      account, streamUrls: urls.length, allowedDomains: true,
    }));
    const start = Date.now();
    const ff = spawnSync('ffmpeg', [
      '-nostdin', '-hide_banner', '-loglevel', 'error', '-y',
      '-rw_timeout', '15000000', '-i', urls[0],
      '-rw_timeout', '15000000', '-i', urls[1],
      '-map', '0:v:0', '-map', '1:a:0', '-c', 'copy',
      '-t', '60', '-movflags', '+faststart', file,
    ], { encoding: 'utf8', timeout: 115000, maxBuffer: 256 * 1024 });
    const durationMs = Date.now() - start;
    let bytes = 0;
    try { bytes = statSync(file).size; } catch {}
    const ffprobe = bytes > 0 ? spawnSync('ffprobe', [
      '-v', 'error', '-show_entries', 'format=duration:stream=codec_type,codec_name',
      '-of', 'json', file,
    ], { encoding: 'utf8', timeout: 12000, maxBuffer: 64 * 1024 }) : null;
    let meta = {};
    try { meta = JSON.parse(ffprobe?.stdout || '{}'); } catch {}
    const tracks = (meta.streams || []).map(s => ({ type: s.codec_type, codec: s.codec_name }));
    const video = tracks.some(s => s.type === 'video');
    const audio = tracks.some(s => s.type === 'audio');
    const seconds = Number(meta.format?.duration || 0);
    const ok = ff.status === 0 && video && audio && seconds >= 50 && bytes > 100000;
    console.log('ONE_MINUTE_RESULT ' + JSON.stringify({
      account, status: ok ? 'VALID_MP4_VIDEO_AUDIO' : 'FAILED',
      ffmpegExitCode: ff.status, ffmpegSpawnError: ff.error?.code || null, ffmpegErrorCategory: ff.status === 0 ? null : category(String(ff.stderr || '')),
      wallClockSeconds: Math.round(durationMs / 1000), mp4Seconds: seconds,
      bytes, video, audio, tracks,
    }));
    if (!ok) process.exitCode = 1;
  }
} finally {
  // The private adult video is never published as a GitHub artifact.
  rmSync(outDir, { recursive: true, force: true });
}
