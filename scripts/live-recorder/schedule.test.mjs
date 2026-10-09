import test from 'node:test';
import assert from 'node:assert/strict';
import { nextPollDelay } from './schedule.mjs';

test('page load time is included in the 60-second polling period', () => {
  assert.equal(nextPollDelay(0, 7_000), 53_000);
  assert.equal(nextPollDelay(0, 10_500), 49_500);
});
test('a slow poll does not cause another 60-second wait', () => {
  assert.equal(nextPollDelay(0, 60_000), 0);
  assert.equal(nextPollDelay(0, 70_000), 0);
});
test('an ordinary 60-second poll starts 60 seconds after the prior start', () => {
  const started = 123_456;
  assert.equal(nextPollDelay(started, started), 60_000);
  assert.equal(nextPollDelay(started, started + 59_999), 1);
});
