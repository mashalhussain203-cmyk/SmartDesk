import { chromium } from 'playwright-core';
import { setTimeout as delay } from 'node:timers/promises';
import { acceptAdultTerms, isAdultTermsScreen } from './age-consent.mjs';

// One-time read-only diagnostic. No recording, screenshots, cookies or stream URLs logged.
const account = 'lucycums';
const url = 'https://chaturbate.com/' + account + '/';
const browser = await chromium.launch({
  executablePath: process.env.CHROME_PATH || '/usr/bin/google-chrome',
  headless: false,
  args: ['--no-sandbox', '--autoplay-policy=no-user-gesture-required', '--disable-dev-shm-usage'],
});
let anyAdvancing = false;
let anyConsentClicked = false;
try {
  const context = await browser.newContext({ viewport: { width: 1280, height: 720 } });
  const page = await context.newPage();
  for (let attempt = 1; attempt <= 2; attempt++) {
    let httpStatus = null, navigationError = null;
    try {
      const response = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 35000 });
      httpStatus = response?.status() ?? null;
    } catch (error) {
      navigationError = String(error?.message || error).split('\n')[0].slice(0, 140);
    }
    await delay(4000);
    const ageScreenBefore = await page.locator('body').innerText({ timeout: 7000 })
      .then(isAdultTermsScreen).catch(() => false);
    const consent = await acceptAdultTerms(page, true).catch(error => ({
      detected: ageScreenBefore, clicked: false, error: String(error?.message || error).slice(0, 100),
    }));
    anyConsentClicked ||= Boolean(consent.clicked);
    if (consent.clicked) await delay(6000);

    for (let sample = 1; sample <= 2; sample++) {
      const body = await page.locator('body').innerText({ timeout: 7000 }).catch(() => '');
      const explicitOffline = /this room is (currently )?offline|user is currently offline|is offline|room is offline/i.test(body);
      const loginGate = /log in to continue|login required|please sign in/i.test(body);
      const challenge = /just a moment|checking your browser|verify you are human|access denied/i.test(body);
      const stats = { total: 0, visible: 0, paused: 0, ready: 0, withSource: 0, mediaErrors: 0, advancing: 0 };
      for (const frame of page.frames()) {
        const videos = await frame.evaluate(async () => {
          const nodes = Array.from(document.querySelectorAll('video'));
          return await Promise.all(nodes.map(async video => {
            if (video.paused && !video.ended && (video.currentSrc || video.srcObject)) {
              await Promise.race([video.play().catch(() => {}), new Promise(r => setTimeout(r, 1000))]);
            }
            const before = video.currentTime;
            await new Promise(r => setTimeout(r, 1250));
            const rect = video.getBoundingClientRect();
            return {
              visible: rect.width >= 240 && rect.height >= 135,
              paused: video.paused,
              ready: video.readyState >= 2,
              withSource: Boolean(video.currentSrc || video.srcObject),
              mediaErrors: Boolean(video.error),
              advancing: !video.paused && video.readyState >= 2 && video.currentTime > before,
            };
          }));
        }).catch(() => []);
        stats.total += videos.length;
        for (const video of videos) for (const key of Object.keys(video)) if (video[key]) stats[key]++;
      }
      anyAdvancing ||= stats.advancing > 0;
      const state = stats.advancing > 0 ? 'VIDEO_PLAYING' :
        challenge || httpStatus === 403 ? 'ACCESS_BLOCKED' :
        isAdultTermsScreen(body) ? 'AGE_GATE_STILL_VISIBLE' :
        loginGate ? 'LOGIN_REQUIRED' : explicitOffline ? 'OFFLINE' : 'NO_PLAYBACK';
      console.log('LUCYCUMS_PROBE', JSON.stringify({
        attempt, sample, checkedAt: new Date().toISOString(), httpStatus,
        navigationError, ageScreenBefore, consent,
        state, frames: page.frames().length, stats,
      }));
      if (sample === 1) await delay(12000);
    }
    if (attempt === 1) await delay(10000);
  }
  console.log('LUCYCUMS_RESULT', JSON.stringify({
    actualVideoPlaybackConfirmed: anyAdvancing, ageTermsClicked: anyConsentClicked,
  }));
} finally {
  await browser.close();
}
