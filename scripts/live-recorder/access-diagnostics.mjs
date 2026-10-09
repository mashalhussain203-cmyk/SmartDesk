/**
 * Privacy-safe browser access diagnostics.
 * Store only HTTP status and request type, never URLs, headers, cookies,
 * response bodies, page text, media URLs, or credentials.
 */
export function newNetworkSummary() {
  return {
    mainHttpStatus: null,
    documentErrors: 0,
    mediaErrors: 0,
    xhrErrors: 0,
    fetchErrors: 0,
    scriptErrors: 0,
    otherErrors: 0,
    failedRequests: 0,
    pageScriptErrors: 0,
    apiErrorCodes: {},
    mediaErrorCodes: {},
  };
}

export function recordHttpResponse(summary, status, type, isMainDocument = false, origin = 'other') {
  if (isMainDocument) summary.mainHttpStatus = status;
  if (!Number.isInteger(status) || status < 400) return;
  const key = {
    document: 'documentErrors',
    media: 'mediaErrors',
    xhr: 'xhrErrors',
    fetch: 'fetchErrors',
    script: 'scriptErrors',
  }[type] || 'otherErrors';
  summary[key]++;
  // Only HTTP code + broad host category; never record actual request URLs.
  const codes = ['xhr', 'fetch'].includes(type) ? summary.apiErrorCodes
    : type === 'media' ? summary.mediaErrorCodes : null;
  if (codes && Object.keys(codes).length < 12) {
    const safeOrigin = ['site', 'media-network'].includes(origin) ? origin : 'other';
    const label = String(status) + ':' + safeOrigin;
    codes[label] = (codes[label] || 0) + 1;
  }
}

export function diagnoseAccess(result, summary) {
  // Never override verified playing video or explicit 18+ setup prompts.
  if (result.kind === 'live' || result.kind === 'recording' || result.kind === 'needs_setup') return result;
  const status = summary.mainHttpStatus;
  if (status === 403 || status === 401 || status === 429) {
    return {
      kind: 'error',
      detail: 'Chaturbate weigert de recorder toegang (HTTP ' + status +
        '). De uitzending kan wel live zijn; opname niet mogelijk vanuit deze browser.',
    };
  }
  if (status >= 400) {
    return {
      kind: 'error',
      detail: 'Chaturbate-pagina niet toegankelijk (HTTP ' + status +
        '); de echte live-status is niet vastgesteld.',
    };
  }
  if (result.kind !== 'unknown') return result;
  const issues = [];
  if (status !== null) issues.push('pagina HTTP ' + status);
  if (summary.mediaErrors) issues.push('mediafouten HTTP ' + summary.mediaErrors +
    ' (' + Object.entries(summary.mediaErrorCodes).map(([code, n]) => code + ' x' + n).join(', ') + ')');
  if (summary.xhrErrors + summary.fetchErrors) {
    issues.push('XHR/fetch HTTP-fouten ' + (summary.xhrErrors + summary.fetchErrors) +
      ' (' + Object.entries(summary.apiErrorCodes).map(([code, n]) => code + ' x' + n).join(', ') + ')');
  }
  if (summary.scriptErrors) issues.push('script HTTP-fouten ' + summary.scriptErrors);
  if (summary.failedRequests) issues.push('netwerkverzoeken mislukt ' + summary.failedRequests);
  if (summary.pageScriptErrors) issues.push('browser-scriptfouten ' + summary.pageScriptErrors);
  return { ...result, detail: result.detail + (issues.length ? '; ' + issues.join(', ') : '') };
}
