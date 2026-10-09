/**
 * Discover the publicly accessible Chaturbate HLS media tracks using yt-dlp.
 * No Chaturbate API token, authentication bypass, cookies, or credentials.
 * Never print or persist the signed URLs; they can contain short-lived tokens.
 */
import { execFile } from 'node:child_process';
import { promisify } from 'node:util';

const exec = promisify(execFile);
const SAFE_HOSTS = ['mmcdn.com', 'highwebmedia.com', 'chaturbate.com'];

export function parsePublicMediaUrls(stdout) {
  const lines = String(stdout || '').split(/\r?\n/).map(s => s.trim()).filter(Boolean);
  if (lines.length < 1 || lines.length > 3) return [];
  const urls = [];
  for (const line of lines) {
    try {
      const u = new URL(line);
      const host = u.hostname.toLowerCase();
      if (u.protocol !== 'https:' || !SAFE_HOSTS.some(h => host === h || host.endsWith('.' + h))) return [];
      urls.push(line);
    } catch { return []; }
  }
  return urls;
}

export function hlsFfmpegArgs(urls) {
  if (!Array.isArray(urls) || urls.length < 2 || !parsePublicMediaUrls(urls.join('\n')).length) {
    throw new Error('Expected separate public HLS video and audio tracks');
  }
  return [
    '-hide_banner', '-loglevel', 'warning', '-nostdin',
    '-rw_timeout', '15000000', '-i', urls[0],
    '-rw_timeout', '15000000', '-i', urls[1],
    '-map', '0:v:0', '-map', '1:a:0',
    '-c:v', 'copy', '-c:a', 'copy',
    '-movflags', '+frag_keyframe+empty_moov+default_base_moof',
    '-f', 'mp4', 'pipe:1',
  ];
}

export async function findPublicMedia(account, run = exec) {
  if (!/^[a-z0-9_]{1,40}$/.test(account)) throw new Error('Invalid account');
  try {
    const { stdout } = await run('yt-dlp', [
      '--no-config', '--no-playlist', '--no-warnings', '--no-progress', '--get-url',
      '-f', 'bv*+ba/b', '--socket-timeout', '12', '--retries', '0', '--extractor-retries', '0',
      'https://chaturbate.com/' + account + '/',
    ], { timeout: 30000, maxBuffer: 128 * 1024 });
    const urls = parsePublicMediaUrls(stdout);
    if (urls.length >= 2) return { kind: 'live', urls, detail: 'Publieke HLS-video en audio gevonden' };
    return { kind: 'unknown', detail: 'Geen afzonderlijke publieke video- en audiobron gevonden' };
  } catch (error) {
    // Do not expose stderr: yt-dlp and FFmpeg can include signed media URLs.
    const stderr = String(error?.stderr || '');
    const reason = /403|forbidden/i.test(stderr) ? 'HTTP 403'
      : /429|too many requests/i.test(stderr) ? 'HTTP 429'
        : /404|not found/i.test(stderr) ? 'HTTP 404'
          : /offline|not broadcasting|not online/i.test(stderr) ? 'stream niet publiek beschikbaar'
            : /timeout|timed out|ETIMEDOUT/i.test(stderr) ? 'time-out'
              : 'geen toegang tot publieke HLS-bron';
    const explicitlyOffline = /offline|not broadcasting|not online/i.test(stderr) && !/403|429|forbidden|too many requests/i.test(stderr);
    return { kind: explicitlyOffline ? 'offline' : 'unknown', detail: 'HLS-controle: ' + reason };
  }
}
