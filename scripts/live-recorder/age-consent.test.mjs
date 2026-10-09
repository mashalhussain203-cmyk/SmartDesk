import test from 'node:test';
import assert from 'node:assert/strict';
import { isAdultTermsScreen, acceptAdultTerms } from './age-consent.mjs';

const modal = 'YOU MUST BE OVER 18 AND AGREE TO THE TERMS BELOW BEFORE CONTINUING: I AGREE Exit this site';
const fakePage = ({ url = 'https://chaturbate.com/knock1knock/', text = modal, visible = true } = {}) => {
  let clicks = 0;
  const locator = {
    first() { return this; },
    async isVisible() { return visible; },
    async click() { clicks++; },
  };
  const frame = {
    locator() { return { ...locator, innerText: async () => text }; },
    getByRole() { return locator; },
    getByText() { return locator; },
  };
  return { url: () => url, frames: () => [frame], get clicks() { return clicks; } };
};

test('recognizes the exact adult terms screen, not a generic 18+ message', () => {
  assert.equal(isAdultTermsScreen(modal), true);
  assert.equal(isAdultTermsScreen('Log in to continue. You must be 18.'), false);
  assert.equal(isAdultTermsScreen('Please verify you are human'), false);
});

test('does not click unless account owner opted in', async () => {
  const page = fakePage();
  assert.deepEqual(await acceptAdultTerms(page, false), { detected: true, clicked: false });
  assert.equal(page.clicks, 0);
});

test('clicks the adult terms button once after explicit consent', async () => {
  const page = fakePage();
  assert.deepEqual(await acceptAdultTerms(page, true), { detected: true, clicked: true });
  assert.equal(page.clicks, 1);
});

test('does not click any other site or unrelated login / CAPTCHA screen', async () => {
  const other = fakePage({ url: 'https://example.org/', text: modal });
  assert.deepEqual(await acceptAdultTerms(other, true), { detected: false, clicked: false });
  assert.equal(other.clicks, 0);
  const login = fakePage({ text: 'Please log in to continue' });
  assert.deepEqual(await acceptAdultTerms(login, true), { detected: false, clicked: false });
  assert.equal(login.clicks, 0);
});

test('does not click invisible buttons', async () => {
  const page = fakePage({ visible: false });
  assert.deepEqual(await acceptAdultTerms(page, true), { detected: true, clicked: false });
  assert.equal(page.clicks, 0);
});
