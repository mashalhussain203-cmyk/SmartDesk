import { spawnSync } from 'node:child_process';

const accounts = ['lucycums', 'knock1knock', 'emyii'];
function classifyFailure(raw) {
  const s = raw.toLowerCase();
  if (/403|forbidden/.test(s)) return 'HTTP_403';
  if (/404|not found/.test(s)) return 'HTTP_404';
  if (/429|too many requests/.test(s)) return 'HTTP_429';
  if (/offline|not broadcasting|not online/.test(s)) return 'ROOM_OFFLINE_REPORTED';
  if (/private|hidden|password/.test(s)) return 'NONPUBLIC_ROOM_REPORTED';
  if (/timeout|timed out/.test(s)) return 'TIMEOUT';
  if (/certificate|ssl/.test(s)) return 'TLS_ERROR';
  if (/impersonat/.test(s)) return 'BROWSER_IMPERSONATION_DEPENDENCY';
  if (/unsupported url|no suitable extractor/.test(s)) return 'UNSUPPORTED_URL';
  return 'OTHER_ERROR';
}
for (const account of accounts) {
  const args = ['--no-config', '--no-playlist', '--no-warnings', '--no-progress',
    '--get-url', '-f', 'bv*+ba/b', '--socket-timeout', '12', '--retries', '0',
    '--extractor-retries', '0', 'https://chaturbate.com/' + account + '/'];
  const start = Date.now();
  const r = spawnSync('yt-dlp', args, { encoding: 'utf8', timeout: 45000, maxBuffer: 256 * 1024 });
  // Never print signed HLS URLs, cookies, room HTML or raw stderr.
  const urls = String(r.stdout || '').split(/\r?\n/).filter(s => /^https:\/\//.test(s));
  const safeDomains = urls.every(s => {
    try {
      const h = new URL(s).hostname.toLowerCase();
      return h === 'mmcdn.com' || h.endsWith('.mmcdn.com') ||
        h === 'highwebmedia.com' || h.endsWith('.highwebmedia.com') ||
        h === 'chaturbate.com' || h.endsWith('.chaturbate.com');
    } catch { return false; }
  });
  const result = {
    account, checkedAt: new Date().toISOString(),
    durationMs: Date.now() - start,
    status: r.status,
    mediaUrlsFound: urls.length,
    allowedMediaDomainsOnly: safeDomains,
    classification: r.status === 0 && urls.length ? 'STREAM_URL_AVAILABLE' :
      r.error?.code === 'ETIMEDOUT' ? 'TIMEOUT' : classifyFailure(String(r.stderr || '')),
  };
  console.log('YTDLP_PROBE ' + JSON.stringify(result));
}
