/**
 * Confirm the ordinary Chaturbate 18+ terms screen ONLY when the account
 * owner explicitly opted in via LIVE_ACCEPT_ADULT_TERMS=1.
 * This is not an identity check, CAPTCHA solver or access-control bypass.
 */
export function isAdultTermsScreen(bodyText = '') {
  return /you\s+must\s+be\s+over\s+18\s+and\s+agree\s+to\s+the\s+terms/i.test(bodyText)
    && /exit\s+this\s+site/i.test(bodyText);
}

export async function acceptAdultTerms(page, optedIn = false) {
  const hostname = (() => {
    try { return new URL(page.url()).hostname.toLowerCase(); } catch { return ''; }
  })();
  if (hostname !== 'chaturbate.com' && hostname !== 'www.chaturbate.com') {
    return { detected: false, clicked: false };
  }
  for (const frame of page.frames()) {
    const body = await frame.locator('body').innerText({ timeout: 3500 }).catch(() => '');
    if (!isAdultTermsScreen(body)) continue;
    if (!optedIn) return { detected: true, clicked: false };
    const buttons = [
      frame.getByRole('button', { name: /^i agree$/i }),
      frame.getByRole('link', { name: /^i agree$/i }),
      frame.locator('input[type="submit"][value="I AGREE"], input[type="button"][value="I AGREE"]'),
      frame.getByText(/^i agree$/i),
    ];
    for (const button of buttons) {
      if (!await button.first().isVisible().catch(() => false)) continue;
      try {
        await button.first().click({ timeout: 5000 });
        return { detected: true, clicked: true };
      } catch {
        // The page may navigate while the modal is being clicked.
        if (!await frame.locator('body').innerText({ timeout: 1500 }).then(isAdultTermsScreen).catch(() => true)) {
          return { detected: true, clicked: true };
        }
      }
    }
    return { detected: true, clicked: false };
  }
  return { detected: false, clicked: false };
}
