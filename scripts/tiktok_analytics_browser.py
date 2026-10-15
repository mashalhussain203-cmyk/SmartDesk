#!/usr/bin/env python3
"""Read only public TikTok pages and page-generated public post responses.

Called by TikTokAnalyticsService. Never authenticates, bypasses challenges, or
fabricates reach/region/recommendation status.
"""
import html
import json
import os
import re
import shutil
import sys
from urllib.parse import quote


def output(obj):
    print(json.dumps(obj, ensure_ascii=False, separators=(",", ":")))
    raise SystemExit(0)


def script_state(markup):
    for sid in ("__UNIVERSAL_DATA_FOR_REHYDRATION__", "SIGI_STATE", "__NEXT_DATA__"):
        match = re.search(
            r'<script\b(?=[^>]*\bid=["\']' + re.escape(sid) +
            r'["\'])[^>]*>(.*?)</script\s*>',
            markup or "", re.I | re.S,
        )
        if not match:
            continue
        try:
            parsed = json.loads(html.unescape(match.group(1)).strip())
            if isinstance(parsed, dict):
                return parsed
        except (ValueError, TypeError):
            pass
    return {}


def profile_user(state):
    scope = state.get("__DEFAULT_SCOPE__", {})
    if not isinstance(scope, dict):
        return {}
    info = scope.get("webapp.user-detail", {})
    if not isinstance(info, dict):
        return {}
    user_info = info.get("userInfo", {})
    return user_info.get("user", {}) if isinstance(user_info, dict) else {}


def walk_items(root, username):
    results = {}
    stack = [(root, 0)]
    scanned = 0
    while stack and scanned < 20000 and len(results) < 90:
        node, depth = stack.pop()
        scanned += 1
        if not isinstance(node, (list, dict)) or depth > 15:
            continue
        if isinstance(node, list):
            stack.extend((value, depth + 1) for value in node if isinstance(value, (dict, list)))
            continue
        video_id = str(node.get("id") or node.get("itemId") or "")
        stats = node.get("stats") or node.get("statsV2") or node.get("statistics")
        if re.fullmatch(r"\d{15,22}", video_id) and isinstance(stats, dict):
            author = node.get("author", {})
            if isinstance(author, dict):
                author = author.get("uniqueId") or author.get("unique_id") or ""
            if not author or str(author).lower().lstrip("@") == username.lower():
                views = stats.get("playCount", stats.get("viewCount"))
                stamp = node.get("createTime") or node.get("create_time")
                if not stamp:
                    stamp = int(video_id) >> 32
                try:
                    if views is not None and 1577836800 < int(stamp) < 2150000000:
                        results[video_id] = {
                            "id": video_id,
                            "views": int(views),
                            "published_at": int(stamp),
                        }
                except (TypeError, ValueError, OverflowError):
                    pass
        stack.extend((value, depth + 1) for value in node.values() if isinstance(value, (dict, list)))
    return results


def find_video(root, vid):
    stack = [(root, 0)]
    scanned = 0
    while stack and scanned < 18000:
        node, depth = stack.pop()
        scanned += 1
        if not isinstance(node, (dict, list)) or depth > 15:
            continue
        if isinstance(node, list):
            stack.extend((value, depth + 1) for value in node if isinstance(value, (dict, list)))
            continue
        if str(node.get("id") or node.get("itemId") or "") == vid and isinstance(
            node.get("stats") or node.get("statsV2") or node.get("statistics"), dict
        ):
            return node
        stack.extend((value, depth + 1) for value in node.values() if isinstance(value, (dict, list)))
    return None


def main():
    if len(sys.argv) < 3 or sys.argv[1] not in ("posts", "video"):
        output({"success": False, "error": "Invalid analytics helper request"})
    mode, target = sys.argv[1:3]
    if mode == "posts":
        username = target.strip().lstrip("@")
        if not re.fullmatch(r"[A-Za-z0-9._]{1,24}", username):
            output({"success": False, "error": "Invalid username"})
        url = "https://www.tiktok.com/@" + quote(username) + "?lang=en"
    else:
        vid = sys.argv[3] if len(sys.argv) > 3 else ""
        if not re.fullmatch(r"\d{10,30}", vid) or not re.fullmatch(
            r"https://(?:www\.)?tiktok\.com/@[A-Za-z0-9._]+/video/\d{10,30}(?:\?.*)?",
            target,
        ):
            output({"success": False, "error": "Invalid video url"})
        url = target
        username = url.split("/@")[1].split("/")[0]

    try:
        from playwright.sync_api import sync_playwright
    except Exception:
        output({"success": False, "error": "Playwright unavailable"})

    executable = os.environ.get("CHROMIUM_PATH") or shutil.which("chromium") or shutil.which("chromium-browser")
    posts = {}
    direct_item = None
    diagnostics = []
    try:
        with sync_playwright() as p:
            opts = {"headless": True, "args": ["--no-sandbox", "--disable-dev-shm-usage"]}
            if executable:
                opts["executable_path"] = executable
            browser = p.chromium.launch(**opts)
            context = browser.new_context(
                viewport={"width": 1280, "height": 900}, locale="en-US",
                timezone_id="Europe/Amsterdam",
            )
            page = context.new_page()
            page.set_default_timeout(9000)

            def receive(response):
                nonlocal direct_item
                request_url = response.url
                if "tiktok.com" not in request_url or not any(
                    term in request_url for term in ("api/post/item_list", "/api/item_list/", "/api/item/detail/")
                ):
                    return
                if response.status != 200:
                    return
                try:
                    data = response.json()
                except Exception:
                    return
                if not isinstance(data, dict):
                    return
                posts.update(walk_items(data, username))
                if mode == "video" and direct_item is None:
                    direct_item = find_video(data, vid)

            page.on("response", receive)
            try:
                page.goto(url, wait_until="domcontentloaded", timeout=15000)
            except Exception as exc:
                diagnostics.append(type(exc).__name__)
            state = script_state(page.content())
            posts.update(walk_items(state, username))
            if mode == "video":
                direct_item = find_video(state, vid)
            if mode == "posts":
                user = profile_user(state)
                secuid = user.get("secUid") or user.get("sec_uid")
                if isinstance(secuid, str) and secuid:
                    try:
                        # Use the same public profile feed that the site loads.
                        query = {
                            "aid": "1988", "app_name": "tiktok_web",
                            "device_platform": "web_pc", "from_page": "user",
                            "count": "30", "cursor": "0", "secUid": secuid,
                        }
                        api = "https://www.tiktok.com/api/post/item_list/"
                        data = page.evaluate("""async ({url,params}) => {
                            const qs = new URLSearchParams(params);
                            const r = await fetch(url + '?' + qs.toString(), {credentials:'include'});
                            if (!r.ok) return null;
                            try { return await r.json(); } catch (_) { return null; }
                        }""", {"url": api, "params": query})
                        if isinstance(data, dict):
                            posts.update(walk_items(data, username))
                    except Exception as exc:
                        diagnostics.append(type(exc).__name__)
                if not posts:
                    try:
                        page.mouse.wheel(0, 1100)
                        page.wait_for_timeout(1300)
                        posts.update(walk_items(script_state(page.content()), username))
                    except Exception:
                        pass
            browser.close()
    except Exception as exc:
        output({"success": False, "error": type(exc).__name__, "posts": list(posts.values()), "item": direct_item})

    output({
        "success": bool(posts) if mode == "posts" else isinstance(direct_item, dict),
        "posts": sorted(posts.values(), key=lambda x: -x["published_at"])[:60],
        "item": direct_item,
        "source": "tiktok-public-browser",
    })


if __name__ == "__main__":
    main()
