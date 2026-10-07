#!/usr/bin/env python3
import html as htmlmod
import json
import re
import sys
from urllib.parse import urlsplit, urlunsplit, urlencode, parse_qsl

try:
    from curl_cffi import requests
except Exception as exc:
    print(json.dumps({"success": False, "message": f"curl_cffi ontbreekt: {exc}"}))
    raise SystemExit(3)


def fail(message, code=2):
    print(json.dumps({"success": False, "message": message}, ensure_ascii=False))
    raise SystemExit(code)


def add_buster(url):
    parts = urlsplit(url)
    q = dict(parse_qsl(parts.query, keep_blank_values=True))
    q["lang"] = "en"
    q["_mashal_live"] = str(__import__("time").time_ns())
    return urlunsplit((parts.scheme or "https", parts.netloc, parts.path, urlencode(q), parts.fragment))


def script_json(page, script_id):
    pattern = re.compile(
        r'<script\\b[^>]*\\bid=["\\\']' + re.escape(script_id) + r'["\\\'][^>]*>(.*?)</script>',
        re.I | re.S,
    )
    m = pattern.search(page)
    if not m:
        return None
    raw = htmlmod.unescape(m.group(1)).strip()
    try:
        return json.loads(raw)
    except Exception:
        return None


def has_stats(obj):
    if not isinstance(obj, dict):
        return False
    stats = obj.get("stats") or obj.get("statsV2") or obj.get("statistics")
    if not isinstance(stats, dict):
        return False
    return any(k in stats for k in ("playCount", "play_count", "viewCount", "view_count", "diggCount", "likeCount"))


def video_matches(obj, video_id):
    if not isinstance(obj, dict):
        return False
    oid = obj.get("id") or obj.get("aweme_id") or obj.get("itemId") or obj.get("item_id")
    return oid is None or str(oid) == str(video_id)


def recursive_find(node, video_id, depth=0):
    if depth > 18:
        return None
    if isinstance(node, dict):
        if has_stats(node) and video_matches(node, video_id):
            return node
        direct = node.get(video_id)
        if isinstance(direct, dict) and has_stats(direct):
            return direct
        for value in node.values():
            if isinstance(value, (dict, list)):
                found = recursive_find(value, video_id, depth + 1)
                if found is not None:
                    return found
    elif isinstance(node, list):
        for value in node:
            if isinstance(value, (dict, list)):
                found = recursive_find(value, video_id, depth + 1)
                if found is not None:
                    return found
    return None


def first_int(*values):
    for v in values:
        if v is None or isinstance(v, bool):
            continue
        try:
            return int(str(v).replace(",", "").strip())
        except Exception:
            pass
    return None


def normalize(item):
    stats = item.get("stats") or item.get("statsV2") or item.get("statistics") or {}
    author = item.get("author")
    author_name = None
    if isinstance(author, dict):
        author_name = author.get("uniqueId") or author.get("unique_id") or author.get("username") or author.get("nickname")
    elif isinstance(author, str):
        author_name = author

    video = item.get("video") if isinstance(item.get("video"), dict) else {}
    thumb = video.get("cover") or video.get("originCover") or video.get("origin_cover") or video.get("dynamicCover")
    if isinstance(thumb, dict):
        urls = thumb.get("urlList") or thumb.get("url_list")
        thumb = urls[0] if isinstance(urls, list) and urls else thumb.get("url")
    if isinstance(thumb, list):
        thumb = thumb[0] if thumb else None

    return {
        "views": first_int(stats.get("playCount"), stats.get("play_count"), stats.get("viewCount"), stats.get("view_count")),
        "likes": first_int(stats.get("diggCount"), stats.get("digg_count"), stats.get("likeCount"), stats.get("like_count")),
        "comments": first_int(stats.get("commentCount"), stats.get("comment_count")),
        "shares": first_int(stats.get("shareCount"), stats.get("share_count")),
    }, author_name, item.get("desc") or item.get("description") or item.get("title"), thumb


def parse_item(page, video_id):
    for sid in ("SIGI_STATE", "__UNIVERSAL_DATA_FOR_REHYDRATION__", "__NEXT_DATA__"):
        data = script_json(page, sid)
        if not isinstance(data, dict):
            continue
        if sid == "SIGI_STATE":
            direct = (data.get("ItemModule") or {}).get(video_id)
            if isinstance(direct, dict) and has_stats(direct):
                return direct
        if sid == "__UNIVERSAL_DATA_FOR_REHYDRATION__":
            scope = data.get("__DEFAULT_SCOPE__") or data.get("DEFAULT_SCOPE") or {}
            detail = scope.get("webapp.video-detail") or {}
            direct = ((detail.get("itemInfo") or {}).get("itemStruct")
                      or (detail.get("item_info") or {}).get("item_struct"))
            if isinstance(direct, dict) and has_stats(direct) and video_matches(direct, video_id):
                return direct
        found = recursive_find(data, video_id)
        if found is not None:
            return found

    # TikTok sometimes changes script IDs. Parse all application/json scripts.
    for raw in re.findall(r'<script\\b[^>]*type=["\\\']application/json["\\\'][^>]*>(.*?)</script>', page, re.I | re.S):
        try:
            data = json.loads(htmlmod.unescape(raw).strip())
        except Exception:
            continue
        found = recursive_find(data, video_id)
        if found is not None:
            return found
    return None


def challenge(page):
    sample = page[:350000].lower()
    return any(x in sample for x in (
        "secsdk-captcha", "verify to continue", "verify you are human", "security verification", "captcha",
    ))


def main():
    if len(sys.argv) < 3:
        fail("gebruik: tiktok_public_fetch.py <url> <video_id>")
    video_url = sys.argv[1]
    video_id = sys.argv[2]

    sess = requests.Session(impersonate="chrome")
    headers = {
        "Accept-Language": "en-US,en;q=0.9,nl;q=0.7",
        "Cache-Control": "no-cache",
        "Pragma": "no-cache",
        "Referer": "https://www.tiktok.com/",
    }

    attempts = [
        add_buster(video_url),
        f"https://www.tiktok.com/embed/v2/{video_id}?lang=en-US&_mashal_live={__import__('time').time_ns()}",
    ]
    problems = []

    for target in attempts:
        try:
            r = sess.get(target, headers=headers, timeout=15, allow_redirects=True)
        except Exception as exc:
            problems.append(f"request fout: {exc}")
            continue
        page = r.text or ""
        if r.status_code < 200 or r.status_code >= 400:
            problems.append(f"HTTP {r.status_code}")
            continue
        if not page.strip():
            problems.append("lege HTML")
            continue
        if challenge(page):
            problems.append("TikTok challenge/captcha")
            continue
        item = parse_item(page, video_id)
        if item is None:
            problems.append("geen hydration itemStruct")
            continue
        stats, author, title, thumb = normalize(item)
        if all(v is None for v in stats.values()):
            problems.append("itemStruct zonder counters")
            continue
        print(json.dumps({
            "success": True,
            "stats": stats,
            "author_name": author,
            "title": title,
            "thumbnail_url": thumb,
            "final_url": str(r.url),
            "http_status": r.status_code,
        }, ensure_ascii=False))
        return

    fail("; ".join(problems[-4:]) or "TikTok gaf geen bruikbare publieke data terug")


if __name__ == "__main__":
    main()
