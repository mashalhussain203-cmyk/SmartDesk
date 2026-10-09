import { open, readdir, stat, unlink } from 'node:fs/promises';
import { basename, join } from 'node:path';
import { setTimeout as delay } from 'node:timers/promises';

const CHUNK_SIZE = 2 * 1024 * 1024;

/** Secure recorder -> Laravel upload. No browser/API credentials are exposed to visitors. */
export class PrivateUploadClient {
  constructor({ base, secret, account }) {
    if (!base || !/^https?:\/\//.test(base)) throw Error('Set LIVE_RECORDER_INGEST_URL');
    if (!secret || secret.length < 32) throw Error('Set LIVE_RECORDER_SECRET (>=32 chars)');
    if (!['emyii', 'knock1knock'].includes(account)) throw Error('Unsupported account');
    this.url = base.replace(/\/+$/, '') + '/' + account;
    this.secret = secret;
  }

  async request(path, { method = 'POST', body, json } = {}) {
    const headers = { Authorization: 'Bearer ' + this.secret };
    if (json !== undefined) {
      body = JSON.stringify(json);
      headers['Content-Type'] = 'application/json';
    } else if (body) {
      headers['Content-Type'] = 'application/octet-stream';
    }

    for (let attempt = 0; attempt < 5; attempt++) {
      try {
        const result = await fetch(this.url + path, {
          method, headers, body, signal: AbortSignal.timeout(60000),
        });
        if (result.ok) return await result.json();
        const detail = (await result.text()).slice(0, 180);
        if (result.status < 500 && result.status !== 429) {
          throw Object.assign(new Error('Upload rejected: HTTP ' + result.status + ' ' + detail), { permanent: true });
        }
        throw new Error('Upload unavailable: HTTP ' + result.status);
      } catch (error) {
        if (error.permanent || attempt === 4) throw error;
        await delay(1500 * (attempt + 1));
      }
    }
  }

  async status(status, message) {
    await this.request('/status', { json: { status, message: message.slice(0, 180) } });
  }

  async uploadFile(path) {
    const filename = basename(path);
    if (!/^[A-Za-z0-9_.-]+\.mp4$/.test(filename)) throw Error('Invalid recording filename');
    const size = (await stat(path)).size;
    if (size < 1 || size > 32212254720) throw Error('File too large or empty');
    const count = Math.ceil(size / CHUNK_SIZE);
    const { id } = await this.request('/uploads', { json: { filename } });
    if (!/^[0-9a-f-]{36}$/.test(id)) throw Error('Unexpected upload id');

    const file = await open(path, 'r');
    try {
      for (let i = 0; i < count; i++) {
        const length = Math.min(CHUNK_SIZE, size - i * CHUNK_SIZE);
        const data = Buffer.allocUnsafe(length);
        const { bytesRead } = await file.read(data, 0, length, i * CHUNK_SIZE);
        if (bytesRead !== length) throw Error('Recording file was modified during upload');
        await this.request('/uploads/' + id + '/parts/' + i, {
          method: 'PUT', body: data,
        });
      }
    } finally {
      await file.close();
    }
    await this.request('/uploads/' + id + '/complete', {
      json: { parts: count, bytes: size },
    });
    return filename;
  }

  async flushPending(directory) {
    let uploaded = 0;
    for (const name of (await readdir(directory)).filter(n => /^[A-Za-z0-9_.-]+\.mp4$/.test(n)).sort()) {
      const path = join(directory, name);
      await this.status('uploading', 'Privévideo uploaden: ' + name);
      await this.uploadFile(path);
      await unlink(path);
      await this.status('offline', 'Opname opgeslagen: ' + name);
      uploaded++;
    }
    return uploaded;
  }
}
