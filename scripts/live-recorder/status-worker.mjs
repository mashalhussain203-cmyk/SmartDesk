/**
 * Shared live recorder worker. Each Railway worker owns a disjoint account set.
 * knock1knock is status-only: automatic recording is permanently disabled.
 */
import { S3Client } from '@aws-sdk/client-s3';
import { runHlsMonitor, reportHlsStatus } from './hls-monitor.mjs';
import { setTimeout as delay } from 'node:timers/promises';

const primary = process.env.LIVE_ACCOUNT;
const accountSets = {
  knock1knock: ['knock1knock'],
  emyii: ['emyii', 'cutefacebigass', 'julesxdann', 'leo_kitty', 'ricasashaa'],
};
if (!accountSets[primary]) throw new Error('LIVE_ACCOUNT must be knock1knock or emyii');

const required = [
  'LIVE_S3_ENDPOINT',
  'LIVE_S3_BUCKET',
  'LIVE_S3_REGION',
  'LIVE_S3_ACCESS_KEY_ID',
  'LIVE_S3_SECRET_ACCESS_KEY',
];
for (const key of required) {
  if (!process.env[key]) throw new Error('Missing required configuration: ' + key);
}
if (!process.env.LIVE_S3_ENDPOINT.startsWith('https://')) {
  throw new Error('Private bucket must use HTTPS');
}

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
const abort = new AbortController();
process.once('SIGTERM', () => abort.abort());
process.once('SIGINT', () => abort.abort());

if (primary === 'knock1knock') {
  console.log('knock1knock status-only worker started; automatic MP4 recording is permanently disabled');
  while (!abort.signal.aborted) {
    try {
      await reportHlsStatus({
        s3, bucket, account: 'knock1knock', state: 'offline',
        message: 'Automatische opnames permanent uitgeschakeld op verzoek van de eigenaar',
      });
    } catch (error) {
      console.error('knock1knock disabled status update failed:', error?.name || 'Error',
        error?.$metadata?.httpStatusCode || 'no-http-status', error?.Code || error?.code || 'no-error-code');
    }
    if (!abort.signal.aborted) {
      await Promise.race([
        delay(60_000),
        new Promise(resolve => abort.signal.addEventListener('abort', resolve, { once: true })),
      ]);
    }
  }
} else {
  const accounts = accountSets[primary];
  console.log(
    'SmartDesk continuous HLS recorder started for',
    accounts.join(', '),
    '- discovery every 60 seconds; private MP4 verification enabled',
  );
  await Promise.all(accounts.map(async (account) => {
    try {
      await runHlsMonitor({ s3, bucket, account, signal: abort.signal });
    } catch (error) {
      console.error(account, 'recorder monitor stopped unexpectedly:', error?.message || error);
      process.exitCode = 1;
    }
  }));
}
