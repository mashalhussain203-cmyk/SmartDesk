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


def parse_visible_results(page):
    script = r"""
    () => {
      const clean = (s) => String(s || '').replace(/\s+/g, ' ').trim();
      const out = [];
      const seen = new Set();

      const anchors = Array.from(
        document.querySelectorAll('a[href*="/tiktok-live-follower-counter/"]')
      );

      for (const a of anchors) {
        const href = a.getAttribute('href') || '';
        const m = href.match(/\/tiktok-live-follower-counter\/([^/?#]+)/i);
        if (!m) continue;

        const username = decodeURIComponent(m[1] || '').replace(/^@/, '');
        if (!/^[A-Za-z0-9._]{1,24}$/.test(username)) continue;

        const key = username.toLowerCase();
        if (seen.has(key)) continue;
        seen.add(key);

        const text = clean(a.textContent);
        const img = a.querySelector('img');
        const avatar = img
          ? (img.currentSrc || img.getAttribute('src') || img.src || null)
          : null;

        const texts = Array.from(a.querySelectorAll('h1,h2,h3,h4,strong,p,span'))
          .map(el => clean(el.textContent))
          .filter(Boolean);

        let displayName = null;
        for (const candidate of texts) {
          const normalized = candidate.toLowerCase().replace(/^@/, '');
          if (normalized === key) continue;
          if (candidate.length > 80) continue;
          displayName = candidate;
          break;
        }

        out.push({
          user_id: null,
          username,
          display_name: displayName || username,
          avatar_url: avatar,
          verified: /verified/i.test(text) || !!a.querySelector('[aria-label*="Verified"], [title*="Verified"]')
        });

        if (out.length >= 8) break;
      }

      return out;
    }
    """

    try:
        results = page.evaluate(script)
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
                    if not (200 <= resp.status < 300):
                        return

                    content_type = (resp.headers.get("content-type") or "").lower()
                    url = resp.url

                    if "json" not in content_type and "tiktok.livecounts.io" not in url.lower():
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

            # Type like a real visitor so React/input handlers receive the
            # same keyboard events as on the public page.
            search_input.fill("")
            try:
                search_input.press_sequentially(query, delay=90)
            except Exception:
                search_input.type(query, delay=90)

            debug["stage"] = "wait_for_search_response"

            deadline = time.time() + 10
            while time.time() < deadline:
                if isinstance(captured.get("search"), (dict, list)):
                    break

                visible = parse_visible_results(page)
                if visible:
                    break

                page.wait_for_timeout(120)

            payload = captured.get("search")
            results = normalize_results(payload)

            source = "livecounts-follower-browser-search"

            if not results:
                results = parse_visible_results(page)
                if results:
                    source = "livecounts-follower-rendered-search"

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
