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


def extract_items(payload):
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


def normalize(payload):
    items = extract_items(payload)
    if not isinstance(items, list):
        return []

    out = []
    seen = set()

    for item in items:
        if not isinstance(item, dict):
            continue

        channel_id = str(
            item.get("id")
            or item.get("channelId")
            or item.get("channel_id")
            or ""
        ).strip()

        if not re.fullmatch(r"UC[A-Za-z0-9_-]{22}", channel_id):
            continue

        if channel_id in seen:
            continue
        seen.add(channel_id)

        title = str(
            item.get("username")
            or item.get("displayName")
            or item.get("display_name")
            or item.get("title")
            or "YouTube-kanaal"
        ).strip()

        avatar = (
            item.get("avatar")
            or item.get("avatarUrl")
            or item.get("avatar_url")
            or item.get("thumbnail")
            or item.get("picture")
        )

        out.append({
            "id": channel_id,
            "title": title or "YouTube-kanaal",
            "avatar": str(avatar) if avatar else None,
        })

        if len(out) >= 8:
            break

    return out


def parse_visible_results(page, query):
    script = r"""
    ({ query }) => {
      const clean = (s) => String(s || '').replace(/\s+/g, ' ').trim();
      const requested = String(query || '').toLowerCase();
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
        'a[href*="/youtube-live-subscriber-counter/"], [role="option"], li, button'
      )).filter(visible);

      for (const el of candidates) {
        const nestedLink = el.matches('a[href]')
          ? el
          : el.querySelector('a[href]');
        const href = (nestedLink && nestedLink.getAttribute('href'))
          || el.getAttribute('data-href')
          || '';
        const text = clean(el.textContent);
        if (!text || text.length > 300) continue;

        const lower = text.toLowerCase();

        let id = null;
        const hrefMatch = href.match(/\/youtube-live-subscriber-counter\/(UC[A-Za-z0-9_-]{22})/);
        if (hrefMatch) id = decodeURIComponent(hrefMatch[1]);

        if (!id) {
          const idMatch = text.match(/(UC[A-Za-z0-9_-]{22})/);
          if (idMatch) id = idMatch[1];
        }

        if (!id || seen.has(id)) continue;
        seen.add(id);

        const img = el.querySelector('img');
        const avatar = img
          ? (img.currentSrc || img.getAttribute('src') || img.src || null)
          : null;

        const labels = Array.from(
          el.querySelectorAll('h1,h2,h3,h4,strong,p,span,div')
        )
          .filter(visible)
          .map(node => clean(node.textContent))
          .filter(Boolean);

        let title = null;
        for (const label of labels) {
          if (label === id || label.length > 100) continue;
          if (/subscribers?|views?|videos?/i.test(label)) continue;
          title = label;
          break;
        }

        out.push({
          id,
          title: title || 'YouTube-kanaal',
          avatar
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
        emit({"success": False, "message": "zoekterm ontbreekt"}, 2)

    query = sys.argv[1].strip()
    if len(query) < 2 or len(query) > 255:
        emit({"success": True, "query": query, "results": []})

    if re.search(r"[\\r\\n\\x00]", query):
        emit({"success": False, "message": "ongeldige zoekterm"}, 2)

    try:
        from playwright.sync_api import sync_playwright
    except Exception as exc:
        emit({
            "success": False,
            "message": f"Playwright import mislukt: {exc}",
        }, 3)

    chromium = (
        os.getenv("CHROMIUM_PATH")
        or shutil.which("chromium")
        or shutil.which("chromium-browser")
        or shutil.which("google-chrome")
    )

    started_at = time.time()

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

            def block_heavy(route):
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

            page.route("**/*", block_heavy)

            captured = {
                "payload": None,
                "url": None,
                "status": None,
                "json_candidates": [],
            }

            def on_response(resp):
                try:
                    url = resp.url
                    lower = url.lower()

                    is_youtube_search = (
                        "api.livecounts.io/youtube-live-subscriber-counter/search/"
                        in lower
                    )

                    if is_youtube_search:
                        captured["url"] = url
                        captured["status"] = resp.status

                    # A provider challenge/rate-limit response may arrive
                    # first. Keep the listener active for a later successful
                    # response initiated by Livecounts' own page JavaScript.
                    if not (200 <= resp.status < 300):
                        return

                    content_type = (
                        resp.headers.get("content-type") or ""
                    ).lower()

                    if not is_youtube_search and "json" not in content_type:
                        return

                    payload = resp.json()
                    results = normalize(payload)

                    if results:
                        captured["payload"] = payload
                        captured["url"] = url
                        captured["status"] = resp.status
                        captured["json_candidates"].append(url)
                except Exception:
                    pass

            page.on("response", on_response)

            try:
                page.goto(
                    "https://livecounts.io/youtube-live-subscriber-counter",
                    wait_until="commit",
                    timeout=5500,
                )
            except Exception as exc:
                debug["navigation_warning"] = str(exc)

            selectors = [
                'input[placeholder="Search Query / Channel URL / Username..."]',
                'input[placeholder*="Search Query"]',
                'input[placeholder*="Channel URL"]',
                'input[placeholder*="Username"]',
                'input[type="search"]',
                'input[type="text"]',
            ]

            search_input = None
            deadline = time.time() + 6

            while time.time() < deadline and search_input is None:
                for selector in selectors:
                    try:
                        locator = page.locator(selector).first
                        if locator.count() and locator.is_visible(timeout=500):
                            search_input = locator
                            break
                    except Exception:
                        pass

                if search_input is None:
                    page.wait_for_timeout(80)

            if search_input is None:
                browser.close()
                emit({
                    "success": False,
                    "message": "Livecounts YouTube zoekveld kon niet worden gevonden.",
                    "debug": debug,
                }, 10)

            # Same interaction pattern as the working TikTok follower search:
            # allow hydration, then type into the exact public search field.
            page.wait_for_timeout(250)

            try:
                search_input.click(timeout=1200)
            except Exception:
                pass

            try:
                search_input.fill(query, timeout=900)
            except Exception:
                try:
                    search_input.press("Control+A")
                    search_input.press("Backspace")
                    search_input.press_sequentially(query, delay=30)
                except Exception:
                    search_input.type(query, delay=30)

            def current_results():
                results = normalize(captured.get("payload"))
                if results:
                    return results, "livecounts-youtube-browser-search"

                visible = parse_visible_results(page, query)
                if visible:
                    return visible, "livecounts-youtube-rendered-search"

                return [], None

            results = []
            source = None

            # Livecounts searches from its own input handlers. Give the
            # page one bounded window to return the real userData payload.
            deadline = time.time() + 2.6
            while time.time() < deadline:
                results, source = current_results()
                if results:
                    break
                page.wait_for_timeout(60)

            # Some builds submit the same search on Enter. This still uses
            # Livecounts' own page/JavaScript; no protected API is called
            # directly by this helper.
            if not results:
                try:
                    search_input.press("Enter")
                    debug["enter_submitted"] = True
                except Exception as exc:
                    debug["enter_warning"] = str(exc)

                deadline = time.time() + 1.8
                while time.time() < deadline:
                    results, source = current_results()
                    if results:
                        break
                    page.wait_for_timeout(100)

            # Last UI-only nudge for React-controlled inputs. The
            # Livecounts page still creates the provider request itself.
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

                deadline = time.time() + 1.0
                while time.time() < deadline:
                    results, source = current_results()
                    if results:
                        break
                    page.wait_for_timeout(60)

            debug["search_url"] = captured.get("url")
            debug["search_status"] = captured.get("status")
            debug["json_candidates"] = captured.get("json_candidates")
            debug["result_count"] = len(results)
            debug["elapsed_ms"] = int((time.time() - started_at) * 1000)
            debug["stage"] = "parsed"

            browser.close()

            emit({
                "success": True,
                "query": query,
                "results": results,
                "source": source or "livecounts-youtube-browser-search",
                "debug": debug,
            })

    except Exception as exc:
        debug["stage"] = "browser_exception"
        debug["message"] = str(exc)
        emit({
            "success": False,
            "message": "YouTube kanaal zoeken mislukt.",
            "debug": debug,
        }, 11)


if __name__ == "__main__":
    main()
