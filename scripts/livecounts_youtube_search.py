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


def extract_channel_list(payload):
    if isinstance(payload, list):
        return payload

    if not isinstance(payload, dict):
        return None

    for key in ("userData", "users", "results", "items", "channels"):
        value = payload.get(key)
        if isinstance(value, list):
            return value

    data = payload.get("data")
    if isinstance(data, list):
        return data

    if isinstance(data, dict):
        for key in ("userData", "users", "results", "items", "channels"):
            value = data.get(key)
            if isinstance(value, list):
                return value

    return None


def normalize_results(payload):
    channels = extract_channel_list(payload)

    if not isinstance(channels, list):
        return []

    out = []
    seen = set()

    for item in channels:
        if not isinstance(item, dict):
            continue

        channel_id = (
            item.get("id")
            or item.get("channelId")
            or item.get("channel_id")
        )

        if not channel_id:
            continue

        channel_id = str(channel_id).strip()

        if not re.fullmatch(r"UC[A-Za-z0-9_-]{22}", channel_id):
            continue

        if channel_id in seen:
            continue
        seen.add(channel_id)

        display_name = (
            item.get("username")
            or item.get("displayName")
            or item.get("display_name")
            or item.get("title")
            or item.get("name")
            or "YouTube-kanaal"
        )

        avatar = (
            item.get("avatar")
            or item.get("avatarUrl")
            or item.get("avatar_url")
            or item.get("thumbnail")
            or item.get("picture")
        )

        out.append({
            "id": channel_id,
            "title": str(display_name).strip() or "YouTube-kanaal",
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

        const lowerText = text.toLowerCase();
        if (
          requested
          && !lowerText.includes(requested)
          && !href.toLowerCase().includes(requested)
        ) {
          continue;
        }

        let id = null;

        const hrefMatch = href.match(
          /\/youtube-live-subscriber-counter\/(UC[A-Za-z0-9_-]{22})/i
        );
        if (hrefMatch) {
          id = decodeURIComponent(hrefMatch[1] || '');
        }

        if (!id) {
          const textMatch = text.match(/(UC[A-Za-z0-9_-]{22})/);
          if (textMatch) {
            id = textMatch[1];
          }
        }

        if (!id || seen.has(id)) continue;
        seen.add(id);

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

        let title = null;
        for (const candidate of textNodes) {
          if (candidate === id || candidate.length > 100) continue;
          if (/subscribers?|views?|videos?|goal/i.test(candidate)) continue;
          title = candidate;
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
        emit({
            "success": False,
            "message": "zoekterm ontbreekt",
            "stage": "args",
        }, 2)

    query = sys.argv[1].strip()

    if len(query) < 2 or len(query) > 255:
        emit({"success": True, "query": query, "results": []})

    if re.search(r"[\r\n\x00]", query):
        emit({
            "success": False,
            "message": "ongeldige zoekterm",
            "stage": "args",
        }, 2)

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

                    if "/youtube-live-subscriber-counter/search" in lower_url:
                        captured["url"] = url
                        captured["status"] = resp.status

                    # Ignore a temporary 429/error and keep listening. This
                    # mirrors the working TikTok follower browser capture.
                    if not (200 <= resp.status < 300):
                        return

                    content_type = (
                        resp.headers.get("content-type") or ""
                    ).lower()

                    if (
                        "json" not in content_type
                        and "api.livecounts.io" not in lower_url
                    ):
                        return

                    payload = resp.json()
                    channels = extract_channel_list(payload)

                    if isinstance(channels, list):
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
                    "https://livecounts.io/youtube-live-subscriber-counter",
                    wait_until="domcontentloaded",
                    timeout=12000,
                )
            except Exception as exc:
                debug["navigation_warning"] = str(exc)

            debug["stage"] = "find_search_input"

            search_input = None
            selectors = [
                'input[placeholder="Search Query / Channel URL / Username..."]',
                'input[placeholder*="Search Query"]',
                'input[placeholder*="Channel URL"]',
                'input[placeholder*="Username"]',
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
                    "message": (
                        "Livecounts YouTube zoekveld kon niet worden gevonden."
                    ),
                    "stage": "search_input_missing",
                    "debug": debug,
                }, 10)

            # Same reliable interaction sequence as TikTok Followers:
            # wait for client hydration, then type like a real visitor.
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
                    return normalized, "livecounts-youtube-browser-search"

                visible = parse_visible_results(page, query)
                if visible:
                    return visible, "livecounts-youtube-rendered-search"

                return [], None

            results = []
            source = None

            # First wait for Livecounts' own debounce/onChange request.
            deadline = time.time() + 4
            while time.time() < deadline:
                results, source = current_results()
                if results:
                    break
                page.wait_for_timeout(120)

            # Same Enter fallback as the working TikTok follower search.
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

            # Final UI-only nudge for React-controlled inputs. We still do
            # not call the protected provider endpoint directly.
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
                "source": (
                    source or "livecounts-youtube-browser-search"
                ),
                "debug": debug,
            })

    except Exception as exc:
        debug["stage"] = "browser_exception"
        debug["message"] = str(exc)

        emit({
            "success": False,
            "message": "YouTube kanaal zoeken mislukt.",
            "stage": "browser_exception",
            "debug": debug,
        }, 11)


if __name__ == "__main__":
    main()
