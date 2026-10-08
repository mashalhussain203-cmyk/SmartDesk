#!/usr/bin/env python3
import json
import os
import re
import shutil
import sys
import time


def emit(payload, code=0):
    print(json.dumps(payload, ensure_ascii=False))
    raise SystemExit(code)


def extract_user_list(payload):
    if isinstance(payload, list):
        return payload

    if not isinstance(payload, dict):
        return None

    for key in ("userData", "users", "results", "items"):
        value = payload.get(key)
        if isinstance(value, list):
            return value

    data = payload.get("data")
    if isinstance(data, list):
        return data

    if isinstance(data, dict):
        for key in ("userData", "users", "results", "items"):
            value = data.get(key)
            if isinstance(value, list):
                return value

    return None


def normalize_results(payload):
    users = extract_user_list(payload)

    if not isinstance(users, list):
        return []

    out = []
    seen = set()

    for item in users:
        if not isinstance(item, dict):
            continue

        username = (
            item.get("id")
            or item.get("uniqueId")
            or item.get("unique_id")
            or item.get("username")
            or item.get("handle")
        )

        if not username:
            continue

        username = str(username).strip().lstrip("@")
        if not re.fullmatch(r"[A-Za-z0-9._]{1,24}", username):
            continue

        key = username.lower()
        if key in seen:
            continue
        seen.add(key)

        display_name = (
            item.get("username")
            or item.get("displayName")
            or item.get("display_name")
            or item.get("nickname")
            or username
        )

        avatar_url = (
            item.get("avatar")
            or item.get("avatarUrl")
            or item.get("avatar_url")
            or item.get("thumbnail")
            or item.get("picture")
        )

        user_id = (
            item.get("userId")
            or item.get("user_id")
            or item.get("uid")
            or item.get("id_str")
        )

        out.append({
            "user_id": str(user_id) if user_id is not None else None,
            "username": username,
            "display_name": str(display_name) if display_name is not None else username,
            "avatar_url": str(avatar_url) if avatar_url else None,
            "verified": bool(item.get("verified", False)),
        })

        if len(out) >= 8:
            break

    return out


def parse_visible_results(page, query):
    script = r"""
    ({ query }) => {
      const clean = (s) => String(s || '').replace(/\s+/g, ' ').trim();
      const requested = String(query || '').toLowerCase().replace(/^@/, '');
      const out = [];
      const seen = new Set();

      const visible = (el) => {
        if (!el) return false;
        const r = el.getBoundingClientRect();
        const st = getComputedStyle(el);
        return r.width > 0
          && r.height > 0
          && st.display !== 'none'
          && st.visibility !== 'hidden';
      };

      const candidates = Array.from(document.querySelectorAll(
        'a[href*="/tiktok-live-follower-counter/"], [role="option"], li, button'
      )).filter(visible);

      for (const el of candidates) {
        const href = el.getAttribute('href') || '';
        const text = clean(el.textContent);
        const lowerText = text.toLowerCase();

        if (!text || text.length > 300) continue;
        if (
          requested
          && !lowerText.includes(requested)
          && !href.toLowerCase().includes(requested)
        ) {
          continue;
        }

        let username = null;
        const hrefMatch = href.match(/\/tiktok-live-follower-counter\/([^/?#]+)/i);
        if (hrefMatch) {
          username = decodeURIComponent(hrefMatch[1] || '').replace(/^@/, '');
        }

        if (!username) {
          const handleMatch = text.match(/@([A-Za-z0-9._]{1,24})/);
          if (handleMatch) {
            username = handleMatch[1];
          }
        }

        if (!username) {
          const tokens = text.split(/\s+/).filter(Boolean);
          for (const token of tokens) {
            const cleaned = token.replace(/^@/, '').replace(/[^A-Za-z0-9._]/g, '');
            if (
              /^[A-Za-z0-9._]{2,24}$/.test(cleaned)
              && cleaned.toLowerCase().includes(requested)
            ) {
              username = cleaned;
              break;
            }
          }
        }

        if (!username || !/^[A-Za-z0-9._]{1,24}$/.test(username)) continue;

        const key = username.toLowerCase();
        if (seen.has(key)) continue;
        seen.add(key);

        const img = el.querySelector('img');
        const avatar = img
          ? (img.currentSrc || img.getAttribute('src') || img.src || null)
          : null;

        const textNodes = Array.from(
          el.querySelectorAll('h1,h2,h3,h4,strong,p,span,div')
        )
          .filter(visible)
          .map(node => clean(node.textContent))
          .filter(Boolean);

        let displayName = null;
        for (const candidate of textNodes) {
          const normalized = candidate.toLowerCase().replace(/^@/, '');
          if (normalized === key) continue;
          if (candidate.includes('@' + username)) continue;
          if (candidate.length > 80) continue;
          displayName = candidate;
          break;
        }

        out.push({
          user_id: null,
          username,
          display_name: displayName || username,
          avatar_url: avatar,
          verified:
            /verified/i.test(text)
            || !!el.querySelector(
              '[aria-label*="Verified"], [title*="Verified"], [data-verified="true"]'
            )
        });

        if (out.length >= 8) break;
      }

      return out;
    }
    """

    try:
        results = page.evaluate(script, {"query": query})
        return results if isinstance(results, list) else []
    except Exception:
        return []


def main():
    if len(sys.argv) < 2:
        emit({"success": False, "message": "zoekterm ontbreekt", "stage": "args"}, 2)

    query = sys.argv[1].strip().lstrip("@")
    if len(query) < 2 or len(query) > 40:
        emit({"success": True, "query": query, "results": []})

    if re.search(r"[\r\n\x00]", query):
        emit({"success": False, "message": "ongeldige zoekterm", "stage": "args"}, 2)

    try:
        from playwright.sync_api import sync_playwright
    except Exception as exc:
        emit({
            "success": False,
            "message": f"Playwright import mislukt: {exc}",
            "stage": "import_playwright",
        }, 3)

    chromium = (
        os.getenv("CHROMIUM_PATH")
        or shutil.which("chromium")
        or shutil.which("chromium-browser")
        or shutil.which("google-chrome")
    )

    debug = {
        "stage": "browser_start",
        "query": query,
        "chromium": chromium,
    }

    try:
        with sync_playwright() as p:
            launch_args = {
                "headless": True,
                "args": [
                    "--no-sandbox",
                    "--disable-dev-shm-usage",
                    "--disable-gpu",
                    "--disable-background-networking",
                    "--disable-default-apps",
                    "--disable-extensions",
                    "--mute-audio",
                ],
            }
            if chromium:
                launch_args["executable_path"] = chromium

            browser = p.chromium.launch(**launch_args)
            context = browser.new_context(
                viewport={"width": 1100, "height": 900},
                locale="en-US",
                user_agent=(
                    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
                    "AppleWebKit/537.36 (KHTML, like Gecko) "
                    "Chrome/141.0.0.0 Safari/537.36"
                ),
            )
            page = context.new_page()

            def block_heavy_assets(route):
                try:
                    if route.request.resource_type in ("image", "media", "font"):
                        route.abort()
                    else:
                        route.continue_()
                except Exception:
                    try:
                        route.continue_()
                    except Exception:
                        pass

            page.route("**/*", block_heavy_assets)

            captured = {
                "search": None,
                "url": None,
                "status": None,
                "json_candidates": [],
            }

            def on_response(resp):
                try:
                    url = resp.url
                    lower_url = url.lower()

                    if "/user/search" in lower_url:
                        captured["url"] = url
                        captured["status"] = resp.status

                    if not (200 <= resp.status < 300):
                        return

                    content_type = (resp.headers.get("content-type") or "").lower()

                    if "json" not in content_type and "tiktok.livecounts.io" not in lower_url:
                        return

                    payload = resp.json()
                    users = extract_user_list(payload)

                    if isinstance(users, list):
                        normalized = normalize_results(payload)

                        if normalized:
                            captured["search"] = payload
                            captured["url"] = url
                            captured["status"] = resp.status
                            captured["json_candidates"].append(url)
                except Exception:
                    pass

            page.on("response", on_response)

            try:
                page.goto(
                    "https://livecounts.io/tiktok-live-follower-counter",
                    wait_until="domcontentloaded",
                    timeout=12000,
                )
            except Exception as exc:
                debug["navigation_warning"] = str(exc)

            debug["stage"] = "find_search_input"

            search_input = None
            selectors = [
                'input[placeholder="Search Accounts"]',
                'input[placeholder*="Search Accounts"]',
                'input[placeholder*="Search accounts"]',
                'input[type="search"]',
                'input[type="text"]',
            ]

            input_deadline = time.time() + 10
            while time.time() < input_deadline and search_input is None:
                for selector in selectors:
                    try:
                        locator = page.locator(selector).first
                        if locator.count() and locator.is_visible(timeout=500):
                            search_input = locator
                            break
                    except Exception:
                        pass

                if search_input is None:
                    page.wait_for_timeout(150)

            if search_input is None:
                browser.close()
                emit({
                    "success": False,
                    "message": "Livecounts zoekveld kon niet worden gevonden.",
                    "stage": "search_input_missing",
                    "debug": debug,
                }, 10)

            # Give the page a moment to finish client-side hydration, then
            # interact with the same visible Search Accounts field a visitor uses.
            page.wait_for_timeout(700)

            try:
                search_input.click(timeout=1200)
            except Exception:
                pass

            try:
                search_input.press("Control+A")
                search_input.press("Backspace")
            except Exception:
                try:
                    search_input.fill("")
                except Exception:
                    pass

            try:
                search_input.press_sequentially(query, delay=110)
            except Exception:
                search_input.type(query, delay=110)

            debug["stage"] = "wait_for_livecounts_search"

            def current_results():
                payload = captured.get("search")
                normalized = normalize_results(payload)
                if normalized:
                    return normalized, "livecounts-follower-browser-search"

                visible = parse_visible_results(page, query)
                if visible:
                    return visible, "livecounts-follower-rendered-search"

                return [], None

            results = []
            source = None

            # First allow the site's own debounce/onChange search to fire.
            deadline = time.time() + 4
            while time.time() < deadline:
                results, source = current_results()
                if results:
                    break
                page.wait_for_timeout(120)

            # Some builds only commit the search after Enter. This is still
            # normal UI interaction; the provider page itself creates the request.
            if not results:
                try:
                    search_input.press("Enter")
                    debug["enter_submitted"] = True
                except Exception as exc:
                    debug["enter_warning"] = str(exc)

                deadline = time.time() + 5
                while time.time() < deadline:
                    results, source = current_results()
                    if results:
                        break
                    page.wait_for_timeout(120)

            # Last UI-only nudge for React-controlled inputs whose handler was
            # attached after the first keystrokes during slow hydration.
            if not results:
                try:
                    search_input.evaluate(
                        """(el) => {
                          el.dispatchEvent(new Event('input', { bubbles: true }));
                          el.dispatchEvent(new Event('change', { bubbles: true }));
                          el.dispatchEvent(new KeyboardEvent('keyup', {
                            key: 'Enter',
                            code: 'Enter',
                            bubbles: true
                          }));
                        }"""
                    )
                    debug["events_redispatched"] = True
                except Exception as exc:
                    debug["event_warning"] = str(exc)

                deadline = time.time() + 3
                while time.time() < deadline:
                    results, source = current_results()
                    if results:
                        break
                    page.wait_for_timeout(120)

            payload = captured.get("search")

            if source is None:
                source = "livecounts-follower-browser-search"

            debug["search_url"] = captured.get("url")
            debug["search_status"] = captured.get("status")
            debug["json_candidates"] = captured.get("json_candidates")
            debug["result_count"] = len(results)
            debug["stage"] = "parsed"

            browser.close()

            emit({
                "success": True,
                "query": query,
                "results": results,
                "source": source,
                "debug": debug,
            })

    except Exception as exc:
        debug["stage"] = "browser_exception"
        debug["message"] = str(exc)
        emit({
            "success": False,
            "message": "TikTok account zoeken mislukt.",
            "stage": "browser_exception",
            "debug": debug,
        }, 11)


if __name__ == "__main__":
    main()
