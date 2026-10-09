import { chromium } from 'playwright-core';
import { setTimeout as delay } from 'node:timers/promises';

// Diagnostic only: no recording, screenshots, cookies, stream URLs or page text logged.
const account = 'leo_kitty';
const browser = await chromium.launch({
  executablePath: process.env.CHROME_PATH || '/usr/bin/google-chrome',
  headless: false,
  args: ['--no-sandbox', '--autoplay-policy=no-user-gesture-required', '--disable-dev-shm-usage'],
});
try {
  const context = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  const page = await context.newPage();
  for (let attempt = 1; attempt <= 2; attempt++) {
    let httpStatus = null;
    let navigationError = null;
    try {
      const response = await page.goto('https://chaturbate.com/' + account + '/', {
        waitUntil: 'domcontentloaded', timeout: 35000,
      });
      httpStatus = response?.status() ?? null;
    } catch (error) {
      navigationError = String(error?.message || error).split('\n')[0].slice(0, 160);
    }
    await delay(12000);
    const body = await page.locator('body').innerText({ timeout: 8000 }).catch(() => '');
    const explicitOffline = /this room is (currently )?offline|user is currently offline|is offline|room is offline/i.test(body);
    const needsSetup = /verify.{0,35}age|age verification|confirm.{0,35}18|you must be 18|log in to continue/i.test(body);
    const botChallenge = /just a moment|checking your browser|verify you are human|access denied/i.test(body);
    const stats = { total: 0, visible: 0, paused: 0, ready: 0, withSource: 0, mediaErrors: 0, advancing: 0 };
    for (const frame of page.frames()) {
      const result = await frame.evaluate(async () => {
        const videos = Array.from(document.querySelectorAll('video'));
        const entries = await Promise.all(videos.map(async v => {
          if (v.paused && (v.currentSrc || v.srcObject)) {
            await Promise.race([v.play().catch(() => {}), new Promise(r => setTimeout(r, 1000))]);
          }
          const time = v.currentTime;
          await new Promise(r => setTimeout(r, 1300));
          const rect = v.getBoundingClientRect();
          return {
            visible: rect.width >= 240 && rect.height >= 135,
            paused: v.paused, ready: v.readyState >= 2,
            withSource: Boolean(v.currentSrc || v.srcObject),
            mediaErrors: Boolean(v.error),
            advancing: !v.paused && v.readyState >= 2 && v.currentTime > time,
          };
        }));
        return entries;
      }).catch(() => []);
      stats.total += result.length;
      for (const entry of result) for (const key of Object.keys(entry)) if (entry[key]) stats[key]++;
    }
    const state = stats.advancing > 0 ? 'LIVE_PLAYING' :
      needsSetup ? 'NEEDS_SETUP' : botChallenge ? 'BLOCKED' : explicitOffline ? 'OFFLINE' : 'UNKNOWN';
    console.log('LEO_KITTY_PROBE', JSON.stringify({
      attempt, checkedAt: new Date().toISOString(), httpStatus, navigationError,
      state, frames: page.frames().length, stats,
    }));
    if (attempt === 1) await delay(25000);
  }
} finally {
  await browser.close();
}
