// Keep a 60-second cadence measured from the start of each poll, not its end.
export function nextPollDelay(startedAt, now = Date.now(), intervalMs = 60_000) {
  return Math.max(0, intervalMs - Math.max(0, now - startedAt));
}
