#!/usr/bin/env python3
import json
import os
import re
import shutil
import sys
import time


TARGET = "https://zefoy.com"


def emit(payload, code=0):
    print(json.dumps(payload, ensure_ascii=False))
    raise SystemExit(code)


def normalize_text(value):
    return re.sub(r"\s+", " ", str(value or "")).strip()


def service_from_text(text):
    lower = text.lower()

    aliases = {
        "hearts": ["hearts", "likes"],
        "comments": ["comment hearts", "comments"],
        "favorites": ["favorites", "favourites", "saves"],
    }

    result = {}

    for key, names in aliases.items():
        matched = None
        for name in names:
            if name in lower:
                matched = name
                break

        if not matched:
            continue

        state = "visible"
        if any(token in lower for token in [
            "unavailable", "disabled", "maintenance", "not available", "offline"
        ]):
            state = "unavailable"
        elif any(token in lower for token in [
            "ready", "available", "online"
        ]):
            state = "available"

        cooldown = None
        cooldown_match = re.search(
            r"(?:wait|cooldown|next|ready in)[^0-9]{0,24}(\d{1,4})\s*(?:s|sec|secs|seconds?)",
            lower,
        )
        if cooldown_match:
            cooldown = int(cooldown_match.group(1))

        result[key] = {
            "state": state,
            "cooldown_seconds": cooldown,
        }

    return result


def merge_service_state(base, extra):
    for key, item in (extra or {}).items():
        if key not in ("hearts", "comments", "favorites"):
            continue
        if not isinstance(item, dict):
            continue

        current = base.get(key, {})
        state = item.get("state") or current.get("state")
        cooldown = item.get("cooldown_seconds")
        if cooldown is None:
            cooldown = current.get("cooldown_seconds")

        base[key] = {
            "state": state or "visible",
            "cooldown_seconds": cooldown,
        }


def extract_from_json(payload):
    if not isinstance(payload, (dict, list)):
        return {}

    text = json.dumps(payload, ensure_ascii=False)
    return service_from_text(text)


def main():
    try:
        from playwright.sync_api import sync_playwright
    except Exception as exc:
        emit({
            "success": False,
            "message": f"Playwright import mislukt: {exc}",
            "stage": "import",
        }, 3)

    chromium = (
        os.getenv("CHROMIUM_PATH")
        or shutil.which("chromium")
        or shutil.which("chromium-browser")
        or shutil.which("google-chrome")
    )

    debug = {
        "stage": "start",
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
                "services": {},
                "json_responses": 0,
                "statuses": [],
            }

            def on_response(resp):
                try:
                    if not (200 <= resp.status < 300):
                        return

                    url = resp.url.lower()
                    content_type = (resp.headers.get("content-type") or "").lower()

                    if "zefoy.com" not in url:
                        return

                    if "json" not in content_type:
                        return

                    payload = resp.json()
                    captured["json_responses"] += 1
                    merge_service_state(
                        captured["services"],
                        extract_from_json(payload),
                    )
                except Exception:
                    pass

            page.on("response", on_response)

            try:
                response = page.goto(
                    TARGET,
                    wait_until="domcontentloaded",
                    timeout=12000,
                )
                if response is not None:
                    debug["http_status"] = response.status
            except Exception as exc:
                debug["navigation_warning"] = str(exc)

            page.wait_for_timeout(1200)

            body_text = ""
            try:
                body_text = normalize_text(page.locator("body").inner_text(timeout=1500))
            except Exception:
                pass

            lower = body_text.lower()
            blocked = any(token in lower for token in [
                "captcha",
                "cloudflare",
                "verify you are human",
                "access denied",
                "forbidden",
            ]) or debug.get("http_status") in (403, 429)

            merge_service_state(
                captured["services"],
                service_from_text(body_text),
            )

            debug["stage"] = "parsed"
            debug["json_responses"] = captured["json_responses"]
            debug["body_prefix"] = body_text[:500]
            debug["blocked"] = blocked

            browser.close()

            emit({
                "success": True,
                "blocked": blocked,
                "services": {
                    "hearts": captured["services"].get(
                        "hearts",
                        {"state": "unknown", "cooldown_seconds": None},
                    ),
                    "comments": captured["services"].get(
                        "comments",
                        {"state": "unknown", "cooldown_seconds": None},
                    ),
                    "favorites": captured["services"].get(
                        "favorites",
                        {"state": "unknown", "cooldown_seconds": None},
                    ),
                },
                "source": "zefoy-public-browser-capture",
                "debug": debug,
            })

    except Exception as exc:
        debug["stage"] = "browser_exception"
        debug["message"] = str(exc)
        emit({
            "success": False,
            "message": "Zefoy publieke pagina kon niet worden gelezen.",
            "stage": "browser_exception",
            "debug": debug,
        }, 11)


if __name__ == "__main__":
    main()
