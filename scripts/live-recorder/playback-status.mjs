/**
 * Merge independent Chrome and public HLS detection without calling a
 * blocked/unreachable stream "offline".
 */
export function mergePlaybackStatus(result, hls) {
  if (hls.kind === 'live') return { kind: 'live', detail: hls.detail };
  if (result.kind !== 'unknown') return result;
  if (hls.kind === 'offline') {
    return { kind: 'offline', detail: 'Uitzending offline bevestigd door publieke streamcontrole' };
  }
  return { ...result, detail: result.detail + '; ' + hls.detail };
}
