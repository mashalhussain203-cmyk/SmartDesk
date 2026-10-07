#!/usr/bin/env python3
import json
import os
import re
import shutil
import sys
import time
from urllib.parse import quote, urlparse


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


def extract_user_id_from_stats_url(url):
    try:
        path = urlparse(url).path
        match = re.search(r"/user/stats/([^/?#]+)", path, re.I)
        if match:
            return match.group(1)
    except Exception:
        pass
    return None


def extract_profile_meta(page, username):
    try:
        return page.evaluate(
            """(username) => {
              const clean = (s) => String(s || '')
                .replace(/\u00a0/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

              const requested = String(username || '')
                .toLowerCase()
                .replace(/^@/, '');

              const visible = (el) => {
                if (!el) return false;
                const r = el.getBoundingClientRect();
                const st = getComputedStyle(el);
                return r.width > 0
                  && r.height > 0
                  && st.display !== 'none'
                  && st.visibility !== 'hidden';
              };

              const all = Array.from(document.querySelectorAll('body *'));

              const usernameNodes = all.filter(el => {
                if (!visible(el) || el.children.length > 3) return false;
                const text = clean(el.textContent).toLowerCase().replace(/^@/, '');
                return text === requested || text.includes('@' + requested);
              });

              let avatar = null;
              let displayName = null;

              for (const node of usernameNodes) {
                let parent = node;

                for (let depth = 0; parent && depth < 6; depth += 1, parent = parent.parentElement) {
                  const imgs = Array.from(parent.querySelectorAll('img'))
                    .filter(img => {
                      const src = img.currentSrc || img.getAttribute('src') || '';
                      const r = img.getBoundingClientRect();
                      return src && r.width >= 32 && r.height >= 32;
                    });

                  if (imgs.length) {
                    avatar = imgs[0];
                  }

                  const candidates = Array.from(parent.querySelectorAll('h1,h2,h3,strong,p,span'))
                    .filter(el => visible(el))
                    .map(el => clean(el.textContent))
                    .filter(Boolean)
                    .filter(text => {
                      const lower = text.toLowerCase().replace(/^@/, '');
                      if (lower === requested) return false;
                      if (lower.includes('followers')) return false;
                      if (lower.includes('following')) return false;
                      if (lower.includes('likes')) return false;
                      if (lower.includes('videos')) return false;
                      if (text.length > 80) return false;
                      return true;
                    });

                  if (candidates.length) {
                    displayName = candidates[0];
                  }

                  if (avatar || displayName) break;
                }

                if (avatar || displayName) break;
              }

              if (!avatar) {
                avatar = Array.from(document.querySelectorAll('img')).find(img => {
                  const src = img.currentSrc || img.getAttribute('src') || '';
                  const alt = clean(img.getAttribute('alt')).toLowerCase();
                  const r = img.getBoundingClientRect();
                  return src
                    && r.width >= 40
                    && r.height >= 40
                    && (
                      alt.includes(requested)
                      || alt.includes('avatar')
                      || src.toLowerCase().includes('avatar')
                    );
                }) || null;
              }

              return {
                display_name: displayName,
                avatar_url: avatar
                  ? (avatar.currentSrc || avatar.getAttribute('src') || avatar.src || null)
                  : null
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

    page_url = (
        "https://livecounts.io/tiktok-live-follower-counter/"
        + quote(username, safe="")
    )

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

            # Same approach as the working video counter:
            # preserve scripts/XHR and skip only heavy visual assets.
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
                "user_id": None,
            }

            def on_response(resp):
                try:
                    url = resp.url

                    if "/user/stats/" in url.lower():
                        captured["stats_url"] = url
                        captured["stats_status"] = resp.status
                        captured["user_id"] = extract_user_id_from_stats_url(url)

                        # Ignore 429/error bodies. Keep listening because the
                        # provider page itself may retry.
                        if 200 <= resp.status < 300:
                            payload = resp.json()
                            if isinstance(payload, dict):
                                captured["stats"] = payload
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
            debug["livecounts_mode"] = "browser-network-capture"
            debug["stage"] = "wait_for_livecounts_user_stats"

            deadline = time.time() + 14

            # Hot path: exactly like the video helper. Wait only for the
            # provider page's own successful JSON response.
            while time.time() < deadline:
                if isinstance(captured.get("stats"), dict):
                    break
                page.wait_for_timeout(100)

            stats_payload = captured.get("stats")
            body_text = ""
            fallback_stats = {}

            # DOM is fallback only, never the primary stats source.
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

                for container in ("data", "stats", "user", "counts"):
                    nested = d.get(container)
                    if isinstance(nested, dict):
                        for key in keys:
                            if key in nested:
                                val = clean_number(nested.get(key))
                                if val is not None:
                                    return val

                return None

            if isinstance(stats_payload, dict):
                followers = pick(
                    stats_payload,
                    "followerCount",
                    "followers",
                    "follower_count",
                )
                likes = pick(
                    stats_payload,
                    "likeCount",
                    "likes",
                    "like_count",
                )
                following = pick(
                    stats_payload,
                    "followingCount",
                    "following",
                    "following_count",
                )
                videos = pick(
                    stats_payload,
                    "videoCount",
                    "videos",
                    "video_count",
                )

                # Be tolerant of the generic Livecounts response shape while
                # still using only the provider's captured JSON.
                bottom_odos = stats_payload.get("bottomOdos")
                if not isinstance(bottom_odos, list):
                    for container in ("data", "stats"):
                        nested = stats_payload.get(container)
                        if (
                            isinstance(nested, dict)
                            and isinstance(nested.get("bottomOdos"), list)
                        ):
                            bottom_odos = nested.get("bottomOdos")
                            break

                if isinstance(bottom_odos, list):
                    if likes is None and len(bottom_odos) > 0:
                        likes = clean_number(bottom_odos[0])
                    if following is None and len(bottom_odos) > 1:
                        following = clean_number(bottom_odos[1])
                    if videos is None and len(bottom_odos) > 2:
                        videos = clean_number(bottom_odos[2])

                stats = {
                    "followers": followers,
                    "likes": likes,
                    "following": following,
                    "videos": videos,
                }
                source = "livecounts-follower-browser-network"
            else:
                stats = {
                    key: clean_number(fallback_stats.get(key))
                    for key in ("followers", "likes", "following", "videos")
                }
                source = "livecounts-follower-public-page-rendered"

            # Profile metadata comes from the exact same public counter page.
            # The image request itself may be blocked for speed; its src still
            # exists in the rendered DOM and can be used by our own frontend.
            meta = extract_profile_meta(page, username)
            display_name = (
                meta.get("display_name")
                if isinstance(meta, dict)
                else None
            )
            avatar_url = (
                meta.get("avatar_url")
                if isinstance(meta, dict)
                else None
            )

            debug["network_stats_captured"] = isinstance(stats_payload, dict)
            debug["network_stats_url"] = captured.get("stats_url")
            debug["network_stats_status"] = captured.get("stats_status")
            debug["resolved_user_id"] = captured.get("user_id")
            debug["stats_keys"] = (
                list(stats_payload.keys())[:30]
                if isinstance(stats_payload, dict)
                else []
            )
            debug["body_prefix"] = body_text[:1000]
            debug["stage"] = "parsed"

            browser.close()

            if any(
                stats.get(key) is None
                for key in ("followers", "likes", "following", "videos")
            ):
                emit({
                    "success": False,
                    "message": (
                        "Livecounts follower response was onvolledig; "
                        "niet alle vier tellers zijn beschikbaar."
                    ),
                    "stage": "livecounts_follower_network_incomplete",
                    "stats": stats,
                    "display_name": display_name,
                    "avatar_url": avatar_url,
                    "debug": debug,
                }, 10)

            emit({
                "success": True,
                "source": source,
                "precision": "raw_integer",
                "username": username,
                "display_name": display_name,
                "avatar_url": avatar_url,
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
