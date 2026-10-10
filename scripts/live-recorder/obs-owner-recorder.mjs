/**
 * Auto-record the owner's own OBS Studio stream and archive the completed MP4.
 * Runs next to OBS (not on the remote Railway recorder). No third-party
 * webpage, streaming platform URL, credential or HLS extraction is involved.
 *
 * Required on the OBS host: Node 22+, OBS websocket enabled, OBS recording
 * format MP4, and the LIVE_S3_* configuration from the private Railway bucket.
 * Run: node scripts/live-recorder/obs-owner-recorder.mjs
 * Do not commit environment variables or bucket credentials.
 */
import { createHash } from 'node:crypto';
import { createReadStream } from 'node:fs';
import { open, stat } from 'node:fs/promises';
import { extname } from 'node:path';
import { setTimeout as sleep } from 'node:timers/promises';
import { S3Client, HeadObjectCommand, PutObjectCommand } from '@aws-sdk/client-s3';
import { Upload } from '@aws-sdk/lib-storage';

const account = 'dellris';
const required = [
  'LIVE_S3_ENDPOINT', 'LIVE_S3_BUCKET', 'LIVE_S3_REGION',
  'LIVE_S3_ACCESS_KEY_ID', 'LIVE_S3_SECRET_ACCESS_KEY',
];
const missing = required.filter(name => !process.env[name]);
if (missing.length) throw new Error('Missing private bucket configuration: ' + missing.join(', '));
if (!process.env.LIVE_S3_ENDPOINT.startsWith('https://')) {
  throw new Error('Private archive endpoint must use HTTPS');
}
const endpoint = new URL(process.env.OBS_WS_URL || 'ws://127.0.0.1:4455');
if (!['ws:', 'wss:'].includes(endpoint.protocol)) throw new Error('OBS_WS_URL must be ws:// or wss://');
if (endpoint.protocol === 'ws:' && !['127.0.0.1', 'localhost', '[::1]'].includes(endpoint.hostname)) {
  throw new Error('Remote OBS connections require encrypted wss://');
}
const s3 = new S3Client({
  endpoint: process.env.LIVE_S3_ENDPOINT,
  region: process.env.LIVE_S3_REGION,
  forcePathStyle: process.env.LIVE_S3_URL_STYLE !== 'virtual-host',
  credentials: {
    accessKeyId: process.env.LIVE_S3_ACCESS_KEY_ID,
    secretAccessKey: process.env.LIVE_S3_SECRET_ACCESS_KEY,
  },
});
const bucket = process.env.LIVE_S3_BUCKET;
let lastSaved = null;
let startedByUs = false;
let lastStatus = null;

const sha = text => createHash('sha256').update(text).digest('base64');
function authResponse(password, salt, challenge) {
  return sha(sha(password + salt) + challenge);
}

class ObsClient {
  constructor() {
    this.socket = null;
    this.pending = new Map();
    this.nextRequest = 0;
    this.dead = false;
  }
  async connect() {
    const socket = new WebSocket(endpoint.toString());
    this.socket = socket;
    let ready;
    const handshake = new Promise((resolve, reject) => {
      ready = { resolve, reject };
    });
    const timeout = setTimeout(() => ready.reject(new Error('OBS WebSocket connection timed out')), 12000);
    socket.addEventListener('message', event => {
      try {
        const msg = JSON.parse(String(event.data));
        if (msg.op === 0) {
          const auth = msg.d?.authentication;
          if (auth && !process.env.OBS_WS_PASSWORD) {
            throw new Error('OBS requires OBS_WS_PASSWORD; provide it outside GitHub');
          }
          socket.send(JSON.stringify({ op: 1, d: {
            rpcVersion: 1,
            eventSubscriptions: 0,
            ...(auth ? { authentication: authResponse(process.env.OBS_WS_PASSWORD, auth.salt, auth.challenge) } : {}),
          } }));
        } else if (msg.op === 2) {
          ready.resolve();
        } else if (msg.op === 7) {
          const request = this.pending.get(msg.d?.requestId);
          if (!request) return;
          this.pending.delete(msg.d.requestId);
          if (msg.d.requestStatus?.result) request.resolve(msg.d.responseData || {});
          else request.reject(new Error(msg.d.requestStatus?.comment || 'OBS request failed'));
        }
      } catch (error) {
        ready.reject(error);
      }
    });
    socket.addEventListener('close', () => {
      this.dead = true;
      ready.reject(new Error('OBS disconnected'));
      for (const request of this.pending.values()) request.reject(new Error('OBS disconnected'));
      this.pending.clear();
    });
    socket.addEventListener('error', () => ready.reject(new Error('Cannot connect to OBS WebSocket')));
    try { await handshake; } finally { clearTimeout(timeout); }
  }
  call(requestType) {
    if (this.dead || this.socket?.readyState !== WebSocket.OPEN) throw new Error('OBS connection unavailable');
    const requestId = 'smartdesk-' + (++this.nextRequest);
    return new Promise((resolve, reject) => {
      const timeout = setTimeout(() => {
        this.pending.delete(requestId);
        reject(new Error('OBS did not respond to ' + requestType));
      }, 12000);
      this.pending.set(requestId, {
        resolve: value => { clearTimeout(timeout); resolve(value); },
        reject: error => { clearTimeout(timeout); reject(error); },
      });
      this.socket.send(JSON.stringify({ op: 6, d: { requestType, requestId, requestData: {} } }));
    });
  }
  close() { this.socket?.close(); }
}

async function report(status, message) {
  const now = new Date().toISOString();
  if (status !== lastStatus) console.log(now, account, status, message);
  lastStatus = status;
  await s3.send(new PutObjectCommand({
    Bucket: bucket,
    Key: 'status/' + account + '.json',
    Body: JSON.stringify({ account, status, message, checked_at: now, last_saved: lastSaved }),
    ContentType: 'application/json',
    CacheControl: 'private, no-store',
  }));
}

async function storeObsVideo(path) {
  if (!path || extname(path).toLowerCase() !== '.mp4') {
    throw new Error('Set OBS recording format to MP4. MKV must not be renamed to MP4.');
  }
  const file = await open(path, 'r');
  let header;
  try {
    header = Buffer.alloc(8);
    const result = await file.read(header, 0, 8, 0);
    if (result.bytesRead < 8 || header.toString('ascii', 4, 8) !== 'ftyp') {
      throw new Error('OBS output is not a valid MP4 container');
    }
  } finally { await file.close(); }
  const info = await stat(path);
  if (info.size < 1024) throw new Error('OBS recording is empty');
  const filename = new Date().toISOString().replace(/[:.]/g, '-') + '_' + account + '_obs.mp4';
  const key = 'recordings/' + account + '/' + filename;
  await new Upload({
    client: s3,
    params: {
      Bucket: bucket, Key: key, Body: createReadStream(path),
      ContentType: 'video/mp4', CacheControl: 'private, no-store',
    },
    queueSize: 2, partSize: 8 * 1024 * 1024, leavePartsOnError: false,
  }).done();
  const saved = await s3.send(new HeadObjectCommand({ Bucket: bucket, Key: key }));
  if (Number(saved.ContentLength) !== info.size) {
    throw new Error('Archive upload verification failed');
  }
  lastSaved = filename;
  return filename;
}

let quitting = false;
for (const event of ['SIGINT', 'SIGTERM']) process.on(event, () => { quitting = true; });
while (!quitting) {
  const obs = new ObsClient();
  try {
    await obs.connect();
    startedByUs = false;
    while (!quitting && !obs.dead) {
      const streaming = (await obs.call('GetStreamStatus')).outputActive === true;
      const currentRecording = (await obs.call('GetRecordStatus')).outputActive === true;
      if (streaming) {
        if (!currentRecording) {
          await obs.call('StartRecord');
          startedByUs = true;
        }
        await report(startedByUs ? 'recording' : 'live',
          startedByUs ? 'OBS neemt jouw uitzending op; MP4 volgt na afloop'
                      : 'OBS neemt al op; bestaande opname wordt niet beheerd');
      } else if (startedByUs) {
        if (!currentRecording) {
          startedByUs = false;
          await report('error', 'OBS-opname is buiten de helper gestopt; controleer het opgeslagen bestand');
        } else {
          const result = await obs.call('StopRecord');
          startedByUs = false;
          await report('uploading', 'OBS-uitzending afgelopen; MP4 wordt privé opgeslagen');
          const filename = await storeObsVideo(result.outputPath);
          await report('offline', 'Eigen OBS-opname veilig opgeslagen: ' + filename);
        }
      } else {
        await report('offline', 'OBS heeft momenteel geen actieve uitzending');
      }
      await sleep(10000);
    }
  } catch (error) {
    console.error('OBS private recorder:', error.message);
    await report('error', 'OBS-koppeling of privéopslag geeft een fout; bekijk lokale helperlogs')
      .catch(e => console.error('Could not update private status:', e.message));
    await sleep(10000);
  } finally { obs.close(); }
}
