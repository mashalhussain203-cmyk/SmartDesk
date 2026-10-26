/**
 * Status worker for the shared emyii recorder service.
 * Most auxiliary rooms remain status-only; ricasashaa is a full HLS recorder.
 */
import { S3Client, PutObjectCommand } from '@aws-sdk/client-s3';
import { findPublicMedia } from './public-hls.mjs';
import { runHlsMonitor } from './hls-monitor.mjs';
import { setTimeout as delay } from 'node:timers/promises';

const primary = process.env.LIVE_ACCOUNT;
const accountSets = {
  knock1knock: ['knock1knock'],
  emyii: ['emyii', 'cutefacebigass', 'julesxdann', 'leo_kitty'],
};
if (!accountSets[primary]) throw new Error('LIVE_ACCOUNT must be knock1knock or emyii');
const required = ['LIVE_S3_ENDPOINT','LIVE_S3_BUCKET','LIVE_S3_REGION','LIVE_S3_ACCESS_KEY_ID','LIVE_S3_SECRET_ACCESS_KEY'];
for (const key of required) if (!process.env[key]) throw new Error('Missing required configuration: '+key);
if (!process.env.LIVE_S3_ENDPOINT.startsWith('https://')) throw new Error('Private bucket must use HTTPS');

const s3 = new S3Client({
  endpoint: process.env.LIVE_S3_ENDPOINT,
  region: process.env.LIVE_S3_REGION,
  forcePathStyle: process.env.LIVE_S3_URL_STYLE !== 'virtual-host',
  maxAttempts: 3,
  credentials: {
    accessKeyId: process.env.LIVE_S3_ACCESS_KEY_ID,
    secretAccessKey: process.env.LIVE_S3_SECRET_ACCESS_KEY,
  },
});
const bucket = process.env.LIVE_S3_BUCKET;
const accounts = accountSets[primary];
const abort = new AbortController();

async function publish(account, status, message) {
  const checked_at = new Date().toISOString();
  await s3.send(new PutObjectCommand({
    Bucket: bucket,
    Key: 'status/' + account + '.json',
    Body: JSON.stringify({ account, status, message, checked_at, recorder_mode: 'status-only' }),
    ContentType: 'application/json',
    CacheControl: 'no-store',
  }));
  console.log(checked_at, account, status, message);
}

async function checkStatusOnly(account) {
  try {
    const source = await findPublicMedia(account);
    if (source.kind === 'live' && Array.isArray(source.urls) && source.urls.length >= 2) {
      await publish(account, 'live', 'Publieke livestreambron gecontroleerd; status-only modus');
    } else if (source.kind === 'offline') {
      await publish(account, 'offline', 'Publieke bron bevestigt dat de stream offline is');
    } else {
      await publish(account, 'unknown', 'Publieke streamstatus niet bevestigd; nieuwe controle volgt binnen een minuut');
    }
  } catch {
    await publish(account, 'unknown', 'Statuscontrole mislukt; automatische nieuwe poging volgt binnen een minuut').catch(() => {});
    console.error(account, 'status check failed');
  }
}

async function statusLoop() {
  while (!abort.signal.aborted) {
    const started = Date.now();
    for (const account of accounts) await checkStatusOnly(account);
    await Promise.race([
      delay(Math.max(1000, 60_000 - (Date.now() - started))),
      new Promise(resolve => abort.signal.addEventListener('abort', resolve, { once: true })),
    ]);
  }
}

// ricasashaa is intentionally NOT part of the status-only list above.
// It gets the continuous HLS recording pipeline: one-minute discovery,
// full-live FFmpeg capture, multipart upload, MP4 verification and private S3 storage.
async function startRicasashaaRecorder() {
  if (primary !== 'emyii') return;
  return runHlsMonitor({
    s3,
    bucket,
    account: 'ricasashaa',
    signal: abort.signal,
  }).catch(error => {
    console.error('ricasashaa recorder stopped unexpectedly:', error?.message || error);
  });
}

process.once('SIGTERM', () => abort.abort());
process.once('SIGINT', () => abort.abort());

console.log('SmartDesk recorder worker started for', accounts.join(', '),
  primary === 'emyii' ? '+ ricasashaa FULL RECORDING' : '');
await Promise.all([statusLoop(), startRicasashaaRecorder()]);
