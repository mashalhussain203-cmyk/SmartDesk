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
  };
}

export function recordHttpResponse(summary, status, type, isMainDocument = false) {
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
  if (summary.mediaErrors) issues.push('mediafouten HTTP ' + summary.mediaErrors);
  if (summary.xhrErrors + summary.fetchErrors) {
    issues.push('XHR/fetch HTTP-fouten ' + (summary.xhrErrors + summary.fetchErrors));
  }
  if (summary.scriptErrors) issues.push('script HTTP-fouten ' + summary.scriptErrors);
  if (summary.failedRequests) issues.push('netwerkverzoeken mislukt ' + summary.failedRequests);
  return { ...result, detail: result.detail + (issues.length ? '; ' + issues.join(', ') : '') };
}
