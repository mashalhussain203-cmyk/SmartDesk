#!/usr/bin/env python3
import json
import os
import re
import shutil
import sys
import time
from urllib.parse import quote


def emit(payload, code=0):
    print(json.dumps(payload, ensure_ascii=False))
    raise SystemExit(code)


def clean_number(value):
    if value is None:
        return None
    digits = re.sub(r"[^0-9]", "", str(value))
    if not digits:
        return None
    try:
        return int(digits)
    except Exception:
        return None


def find_near_label(lines, label):
    target = label.lower()
    for idx, line in enumerate(lines):
        normalized = line.strip().lower()
        if not (normalized == target or normalized.startswith(target + " ")):
            continue

        candidates = []
        for distance in range(1, 9):
            if idx - distance >= 0:
                candidates.append(lines[idx - distance])
            if idx + distance < len(lines):
                candidates.append(lines[idx + distance])

        for candidate in candidates:
            if re.fullmatch(r"[0-9][0-9\s.,]*", candidate.strip()):
                number = clean_number(candidate)
                if number is not None:
                    return number
    return None


def parse_body_text(text):
    lines = [line.strip() for line in text.splitlines() if line.strip()]
    return {
        "followers": find_near_label(lines, "Followers"),
        "likes": find_near_label(lines, "Likes"),
        "following": find_near_label(lines, "Following"),
        "videos": find_near_label(lines, "Videos"),
    }


def extract_profile_meta(page, username):
    try:
        return page.evaluate(
            """(username) => {
              const clean = (s) => String(s || '').replace(/\s+/g, ' ').trim();
              const imgs = Array.from(document.querySelectorAll('img'));
              const avatar = imgs.find(img => {
                const alt = clean(img.getAttribute('alt')).toLowerCase();
                return alt.includes("avatar");
              });

              const texts = Array.from(document.querySelectorAll('h1,h2,h3,strong,p,span'))
                .map(el => clean(el.textContent))
                .filter(Boolean);

              const handle = '@' + String(username || '').toLowerCase();
              let displayName = null;

              for (const text of texts) {
                const lower = text.toLowerCase();
                if (lower === handle) continue;
                if (lower.includes('followers') || lower.includes('likes') || lower.includes('following') || lower.includes('videos')) continue;
                if (text.length > 1 && text.length < 80) {
                  displayName = text;
                  break;
                }
              }

              return {
                display_name: displayName,
                avatar_url: avatar ? (avatar.currentSrc || avatar.src || null) : null
              };
            }""",
            username,
        )
    except Exception:
        return {}


def main():
    if len(sys.argv) < 2:
        emit({"success": False, "message": "username ontbreekt", "stage": "args"}, 2)

    username = sys.argv[1].strip().lstrip("@")
    if not re.fullmatch(r"[A-Za-z0-9._]{1,24}", username):
        emit({"success": False, "message": "ongeldige TikTok username", "stage": "args"}, 2)

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

    page_url = "https://livecounts.io/tiktok-live-follower-counter/" + quote(username, safe="")
    debug = {
        "stage": "browser_start",
        "username": username,
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
                viewport={"width": 1280, "height": 1400},
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
                "stats": None,
                "stats_url": None,
                "stats_status": None,
            }

            def on_response(resp):
                try:
                    url = resp.url
                    if "/user/stats/" in url:
                        payload = resp.json()
                        if isinstance(payload, dict):
                            captured["stats"] = payload
                            captured["stats_url"] = url
                            captured["stats_status"] = resp.status
                except Exception:
                    pass

            page.on("response", on_response)

            response = None
            try:
                response = page.goto(
                    page_url,
                    wait_until="commit",
                    timeout=8000,
                )
            except Exception as nav_exc:
                debug["navigation_warning"] = str(nav_exc)

            debug["page_http_status"] = response.status if response else None
            debug["final_url"] = page.url
            debug["stage"] = "wait_for_user_stats"

            deadline = time.time() + 10
            while time.time() < deadline:
                if isinstance(captured.get("stats"), dict):
                    break
                page.wait_for_timeout(100)

            stats_payload = captured.get("stats")
            body_text = ""
            fallback_stats = {}

            if not isinstance(stats_payload, dict):
                try:
                    body_text = page.locator("body").inner_text(timeout=1200)
                    fallback_stats = parse_body_text(body_text)
                except Exception:
                    pass

            def pick(d, *keys):
                if not isinstance(d, dict):
                    return None
                for key in keys:
                    if key in d:
                        val = clean_number(d.get(key))
                        if val is not None:
                            return val
                for container in ("data", "stats", "user"):
                    nested = d.get(container)
                    if isinstance(nested, dict):
                        for key in keys:
                            if key in nested:
                                val = clean_number(nested.get(key))
                                if val is not None:
                                    return val
                return None

            if isinstance(stats_payload, dict):
                stats = {
                    "followers": pick(stats_payload, "followers", "followerCount", "follower_count"),
                    "likes": pick(stats_payload, "likes", "heartCount", "heart_count"),
                    "following": pick(stats_payload, "following", "followingCount", "following_count"),
                    "videos": pick(stats_payload, "videos", "videoCount", "video_count"),
                }
                source = "livecounts-follower-browser-network"
            else:
                stats = {
                    key: clean_number(fallback_stats.get(key))
                    for key in ("followers", "likes", "following", "videos")
                }
                source = "livecounts-follower-public-page-rendered"

            meta = extract_profile_meta(page, username)

            debug["network_stats_captured"] = isinstance(stats_payload, dict)
            debug["network_stats_url"] = captured.get("stats_url")
            debug["network_stats_status"] = captured.get("stats_status")
            debug["stats_keys"] = list(stats_payload.keys())[:30] if isinstance(stats_payload, dict) else []
            debug["body_prefix"] = body_text[:1000]
            debug["stage"] = "parsed"

            browser.close()

            if any(stats.get(k) is None for k in ("followers", "likes", "following", "videos")):
                emit({
                    "success": False,
                    "message": "Follower counter leverde niet alle vier de tellers.",
                    "stage": "follower_stats_incomplete",
                    "stats": stats,
                    "debug": debug,
                }, 10)

            emit({
                "success": True,
                "source": source,
                "precision": "raw_integer",
                "username": username,
                "display_name": meta.get("display_name") if isinstance(meta, dict) else None,
                "avatar_url": meta.get("avatar_url") if isinstance(meta, dict) else None,
                "stats": stats,
                "debug": debug,
            })

    except Exception as exc:
        debug["stage"] = "browser_exception"
        debug["message"] = str(exc)
        emit({
            "success": False,
            "message": "TikTok follower browser-rendering mislukt.",
            "stage": "browser_exception",
            "debug": debug,
        }, 11)


if __name__ == "__main__":
    main()
