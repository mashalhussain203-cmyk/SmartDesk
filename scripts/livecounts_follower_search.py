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


def normalize_results(payload):
    users = None

    if isinstance(payload, dict):
        users = (
            payload.get("userData")
            or payload.get("users")
            or payload.get("data")
        )
    elif isinstance(payload, list):
        users = payload

    if isinstance(users, dict):
        users = (
            users.get("userData")
            or users.get("users")
            or users.get("data")
        )

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
        )
        if not username:
            continue

        username = str(username).strip().lstrip("@")
        if not username:
            continue

        key = username.lower()
        if key in seen:
            continue
        seen.add(key)

        display_name = (
            item.get("username")
            or item.get("displayName")
            or item.get("display_name")
            or username
        )
        avatar_url = (
            item.get("avatar")
            or item.get("avatarUrl")
            or item.get("avatar_url")
            or item.get("thumbnail")
        )
        user_id = (
            item.get("userId")
            or item.get("user_id")
            or item.get("uid")
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
            }

            def on_response(resp):
                try:
                    url = resp.url
                    if "/user/search" not in url.lower():
                        return

                    captured["url"] = url
                    captured["status"] = resp.status

                    if 200 <= resp.status < 300:
                        payload = resp.json()
                        if isinstance(payload, (dict, list)):
                            captured["search"] = payload
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

            for selector in selectors:
                try:
                    locator = page.locator(selector).first
                    if locator.count() and locator.is_visible(timeout=500):
                        search_input = locator
                        break
                except Exception:
                    pass

            if search_input is None:
                browser.close()
                emit({
                    "success": False,
                    "message": "Livecounts zoekveld kon niet worden gevonden.",
                    "stage": "search_input_missing",
                    "debug": debug,
                }, 10)

            search_input.fill(query)
            debug["stage"] = "wait_for_search_response"

            deadline = time.time() + 8
            while time.time() < deadline:
                if isinstance(captured.get("search"), (dict, list)):
                    break
                page.wait_for_timeout(100)

            payload = captured.get("search")
            results = normalize_results(payload)

            debug["search_url"] = captured.get("url")
            debug["search_status"] = captured.get("status")
            debug["result_count"] = len(results)
            debug["stage"] = "parsed"

            browser.close()

            if not isinstance(payload, (dict, list)):
                emit({
                    "success": False,
                    "message": "Livecounts gaf geen account-zoekresultaten terug.",
                    "stage": "search_response_missing",
                    "debug": debug,
                }, 10)

            emit({
                "success": True,
                "query": query,
                "results": results,
                "source": "livecounts-follower-browser-search",
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
