import test from 'node:test';
import assert from 'node:assert/strict';
import { chromium } from 'playwright-core';
import { acceptAdultTerms } from './age-consent.mjs';

const url = 'https://chaturbate.com/knock1knock/';
const fixture = '<!doctype html><html><body><section id="gate">' +
  '<h1>YOU MUST BE OVER 18 AND AGREE TO THE TERMS BELOW BEFORE CONTINUING:</h1>' +
  '<button id="agree" onclick="localStorage.setItem(\'accepted\', \'yes\'); document.getElementById(\'gate\').remove()">I AGREE</button>' +
  '<a href="#" id="exit">Exit this site</a></section></body></html>';

test('real Chrome clicks 18+ consent and remembers it only after opt-in', async () => {
  const browser = await chromium.launch({
    executablePath: process.env.CHROME_PATH || '/usr/bin/google-chrome',
    headless: true,
    args: ['--no-sandbox', '--disable-dev-shm-usage'],
  });
  try {
    const page = await browser.newPage();
    await page.route(url, route => route.fulfill({ status: 200, contentType: 'text/html', body: fixture }));
    await page.goto(url, { waitUntil: 'domcontentloaded' });
    assert.equal(new URL(page.url()).hostname, 'chaturbate.com');
    assert.match(await page.locator('body').innerText(), /you must be over 18/i);

    assert.deepEqual(await acceptAdultTerms(page, false), { detected: true, clicked: false });
    assert.equal(await page.locator('#gate').count(), 1);
    assert.equal(await page.evaluate(() => localStorage.getItem('accepted')), null);

    assert.deepEqual(await acceptAdultTerms(page, true), { detected: true, clicked: true });
    assert.equal(await page.locator('#gate').count(), 0);
    assert.equal(await page.evaluate(() => localStorage.getItem('accepted')), 'yes');
    assert.deepEqual(await acceptAdultTerms(page, true), { detected: false, clicked: false });
  } finally {
    await browser.close();
  }
});
