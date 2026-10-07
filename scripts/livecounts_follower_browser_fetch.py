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


def choose_user(search_payload, username):
    users = None

    if isinstance(search_payload, dict):
        users = (
            search_payload.get("userData")
            or search_payload.get("users")
            or search_payload.get("data")
        )
    elif isinstance(search_payload, list):
        users = search_payload

    if isinstance(users, dict):
        users = (
            users.get("userData")
            or users.get("users")
            or users.get("data")
        )

    if not isinstance(users, list):
        return None

    requested = username.lower().lstrip("@")
    fallback = None

    for item in users:
        if not isinstance(item, dict):
            continue

        if fallback is None:
            fallback = item

        candidates = [
            item.get("id"),
            item.get("username"),
            item.get("uniqueId"),
            item.get("unique_id"),
        ]

        for candidate in candidates:
            if candidate is None:
                continue
            if str(candidate).lower().lstrip("@") == requested:
                return item

    return fallback


def profile_from_user(item, username):
    if not isinstance(item, dict):
        return {
            "username": username,
            "display_name": None,
            "avatar_url": None,
            "user_id": None,
        }

    handle = (
        item.get("id")
        or item.get("uniqueId")
        or item.get("unique_id")
        or username
    )
    display_name = (
        item.get("username")
        or item.get("displayName")
        or item.get("display_name")
        or handle
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

    return {
        "username": str(handle).lstrip("@"),
        "display_name": display_name,
        "avatar_url": avatar_url,
        "user_id": str(user_id) if user_id is not None else None,
    }


def extract_profile_meta(page, username):
    try:
        return page.evaluate(
            """(username) => {
              const clean = (s) => String(s || '').replace(/\s+/g, ' ').trim();
              const requested = String(username || '').toLowerCase().replace(/^@/, '');
              const imgs = Array.from(document.querySelectorAll('img'));

              let avatar = imgs.find(img => {
                const alt = clean(img.getAttribute('alt')).toLowerCase();
                const src = img.getAttribute('src') || '';
                return alt.includes(requested)
                  || alt.includes('avatar')
                  || src.includes('avatar');
              });

              const texts = Array.from(document.querySelectorAll('h1,h2,h3,strong,p,span'))
                .map(el => clean(el.textContent))
                .filter(Boolean);

              let displayName = null;

              for (const text of texts) {
                const lower = text.toLowerCase();
                if (lower === '@' + requested || lower === requested) continue;
                if (
                  lower === 'followers'
                  || lower === 'likes'
                  || lower === 'following'
                  || lower === 'videos'
                ) continue;
                if (text.length > 1 && text.length < 80) {
                  displayName = text;
                  break;
                }
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

    base_url = "https://livecounts.io/tiktok-live-follower-counter"
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
                    # Keep JavaScript/XHR intact. The avatar URL is obtained from
                    # the provider's JSON, so downloading image bytes here is not
                    # necessary for resolving the profile.
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
                "search": None,
                "search_url": None,
                "search_status": None,
            }

            def on_response(resp):
                try:
                    url = resp.url
                    lower_url = url.lower()

                    # Support both /user/search/{query} and /user/search?query=...
                    if "/user/search" in lower_url:
                        captured["search_url"] = url
                        captured["search_status"] = resp.status

                        if 200 <= resp.status < 300:
                            payload = resp.json()
                            if isinstance(payload, (dict, list)):
                                captured["search"] = payload

                    # Support both /user/stats/{id} and query-string variants.
                    elif "/user/stats" in lower_url:
                        captured["stats_url"] = url
                        captured["stats_status"] = resp.status

                        # Ignore 429/error bodies and keep listening. The site's
                        # own frontend can retry without us forging any request.
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
                    base_url,
                    wait_until="domcontentloaded",
                    timeout=12000,
                )
            except Exception as nav_exc:
                debug["navigation_warning"] = str(nav_exc)

            debug["page_http_status"] = response.status if response else None
            debug["base_final_url"] = page.url
            debug["stage"] = "search_account"

            # Reproduce the provider's own normal UI flow: search an account,
            # allow its own JS to resolve the TikTok userId, then open the result.
            search_input = None
            selectors = [
                'input[placeholder*="Search Accounts"]',
                'input[placeholder*="Search accounts"]',
                'input[placeholder*="Search"]',
                'input[type="search"]',
                'input[type="text"]',
            ]

            for selector in selectors:
                try:
                    locator = page.locator(selector).first
                    if locator.count() > 0 and locator.is_visible(timeout=500):
                        search_input = locator
                        break
                except Exception:
                    pass

            clicked_result = False

            if search_input is not None:
                try:
                    search_input.fill(username)
                    page.wait_for_timeout(900)
                except Exception as exc:
                    debug["search_fill_warning"] = str(exc)

                # Give the provider's own debounced search request time to finish.
                search_deadline = time.time() + 7
                while time.time() < search_deadline:
                    if isinstance(captured.get("search"), (dict, list)):
                        break
                    page.wait_for_timeout(100)

                chosen_user = choose_user(captured.get("search"), username)
                profile = profile_from_user(chosen_user, username)

                try:
                    links = page.locator('a[href*="/tiktok-live-follower-counter/"]')
                    link_count = links.count()

                    best_index = None
                    requested = username.lower()

                    for idx in range(link_count):
                        link = links.nth(idx)
                        href = (link.get_attribute("href") or "").lower()
                        text = (link.inner_text(timeout=300) or "").lower()

                        if requested in href or requested in text:
                            best_index = idx
                            break

                        if profile.get("user_id") and profile["user_id"].lower() in href:
                            best_index = idx
                            break

                    if best_index is None and link_count > 0:
                        best_index = 0

                    if best_index is not None:
                        links.nth(best_index).click(timeout=3000)
                        clicked_result = True
                        page.wait_for_timeout(300)
                except Exception as exc:
                    debug["result_click_warning"] = str(exc)

                # Some versions only submit/navigate after Enter.
                if not clicked_result:
                    try:
                        search_input.press("Enter")
                        page.wait_for_timeout(1000)
                    except Exception:
                        pass

                    try:
                        links = page.locator('a[href*="/tiktok-live-follower-counter/"]')
                        if links.count() > 0:
                            links.first.click(timeout=2500)
                            clicked_result = True
                            page.wait_for_timeout(300)
                    except Exception:
                        pass

            else:
                profile = profile_from_user(None, username)
                debug["search_input_missing"] = True

            # If the UI did not expose a clickable result, the public username
            # route is still a normal supported provider page.
            if not clicked_result:
                direct_url = base_url + "/" + username
                try:
                    page.goto(
                        direct_url,
                        wait_until="commit",
                        timeout=8000,
                    )
                except Exception as nav_exc:
                    debug["direct_navigation_warning"] = str(nav_exc)

            debug["counter_final_url"] = page.url
            debug["clicked_search_result"] = clicked_result
            debug["stage"] = "wait_for_user_stats"

            deadline = time.time() + 14
            while time.time() < deadline:
                if isinstance(captured.get("stats"), dict):
                    break
                page.wait_for_timeout(100)

            stats_payload = captured.get("stats")
            search_payload = captured.get("search")
            chosen_user = choose_user(search_payload, username)
            profile = profile_from_user(chosen_user, username)

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

                # Some Livecounts counter APIs expose the headline count plus
                # the remaining three counters in bottomOdos.
                bottom_odos = stats_payload.get("bottomOdos")
                if not isinstance(bottom_odos, list):
                    for container in ("data", "stats"):
                        nested = stats_payload.get(container)
                        if isinstance(nested, dict) and isinstance(nested.get("bottomOdos"), list):
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

            display_name = profile.get("display_name")
            avatar_url = profile.get("avatar_url")
            resolved_user_id = profile.get("user_id")
            resolved_username = profile.get("username") or username

            if not display_name or not avatar_url:
                meta = extract_profile_meta(page, username)
                if isinstance(meta, dict):
                    display_name = display_name or meta.get("display_name")
                    avatar_url = avatar_url or meta.get("avatar_url")

            debug["network_stats_captured"] = isinstance(stats_payload, dict)
            debug["network_stats_url"] = captured.get("stats_url")
            debug["network_stats_status"] = captured.get("stats_status")
            debug["network_search_captured"] = isinstance(search_payload, (dict, list))
            debug["network_search_url"] = captured.get("search_url")
            debug["network_search_status"] = captured.get("search_status")
            debug["resolved_user_id"] = resolved_user_id
            debug["resolved_username"] = resolved_username
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
                    "message": "Follower counter leverde niet alle vier de tellers.",
                    "stage": "follower_stats_incomplete",
                    "stats": stats,
                    "display_name": display_name,
                    "avatar_url": avatar_url,
                    "debug": debug,
                }, 10)

            emit({
                "success": True,
                "source": source,
                "precision": "raw_integer",
                "username": resolved_username,
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
