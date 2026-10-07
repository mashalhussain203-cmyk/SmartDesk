#!/usr/bin/env python3
import html as htmlmod
import json
import re
import sys
import time
from urllib.parse import urlsplit, urlunsplit, urlencode, parse_qsl


def emit(obj, exit_code=0):
    print(json.dumps(obj, ensure_ascii=False))
    raise SystemExit(exit_code)

try:
    from curl_cffi import requests
except Exception as exc:
    emit({
        "success": False,
        "stage": "import_curl_cffi",
        "message": f"curl_cffi kon niet worden geïmporteerd: {exc}",
        "python": sys.executable,
        "python_version": sys.version.split()[0],
    }, 3)


def add_buster(url):
    parts = urlsplit(url)
    q = dict(parse_qsl(parts.query, keep_blank_values=True))
    q["lang"] = "en"
    q["_mashal_live"] = str(time.time_ns())
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
    return isinstance(stats, dict) and any(
        k in stats for k in ("playCount", "play_count", "viewCount", "view_count", "diggCount", "likeCount")
    )


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
    detected_scripts = []
    for sid in ("SIGI_STATE", "__UNIVERSAL_DATA_FOR_REHYDRATION__", "__NEXT_DATA__"):
        data = script_json(page, sid)
        if isinstance(data, dict):
            detected_scripts.append(sid)
        else:
            continue
        if sid == "SIGI_STATE":
            direct = (data.get("ItemModule") or {}).get(video_id)
            if isinstance(direct, dict) and has_stats(direct):
                return direct, detected_scripts, sid
        if sid == "__UNIVERSAL_DATA_FOR_REHYDRATION__":
            scope = data.get("__DEFAULT_SCOPE__") or data.get("DEFAULT_SCOPE") or {}
            detail = scope.get("webapp.video-detail") or {}
            direct = ((detail.get("itemInfo") or {}).get("itemStruct")
                      or (detail.get("item_info") or {}).get("item_struct"))
            if isinstance(direct, dict) and has_stats(direct) and video_matches(direct, video_id):
                return direct, detected_scripts, sid
        found = recursive_find(data, video_id)
        if found is not None:
            return found, detected_scripts, sid

    app_json_count = 0
    for raw in re.findall(r'<script\\b[^>]*type=["\\\']application/json["\\\'][^>]*>(.*?)</script>', page, re.I | re.S):
        app_json_count += 1
        try:
            data = json.loads(htmlmod.unescape(raw).strip())
        except Exception:
            continue
        found = recursive_find(data, video_id)
        if found is not None:
            return found, detected_scripts + [f"application/json:{app_json_count}"], "application/json"
    return None, detected_scripts + ([f"application/json_count={app_json_count}"] if app_json_count else []), None


def challenge_reason(page):
    sample = page[:350000].lower()
    needles = (
        "secsdk-captcha", "verify to continue", "verify you are human",
        "security verification", "captcha", "access denied", "robot",
    )
    for needle in needles:
        if needle in sample:
            return needle
    return None


def main():
    if len(sys.argv) < 3:
        emit({"success": False, "stage": "arguments", "message": "Gebruik: tiktok_public_fetch.py <url> <video_id>"}, 2)

    video_url = sys.argv[1]
    video_id = sys.argv[2]
    session = requests.Session(impersonate="chrome")
    headers = {
        "Accept-Language": "en-US,en;q=0.9,nl;q=0.7",
        "Cache-Control": "no-cache",
        "Pragma": "no-cache",
        "Referer": "https://www.tiktok.com/",
    }
    targets = [
        ("public_video_page", add_buster(video_url)),
        ("public_embed_v2", f"https://www.tiktok.com/embed/v2/{video_id}?lang=en-US&_mashal_live={time.time_ns()}"),
    ]
    attempts = []

    for source, target in targets:
        attempt = {"source": source, "target": target}
        started = time.monotonic()
        try:
            r = session.get(target, headers=headers, timeout=15, allow_redirects=True)
            attempt.update({
                "request_ok": True,
                "http_status": int(r.status_code),
                "final_url": str(r.url),
                "content_type": r.headers.get("content-type"),
                "content_length": len(r.content or b""),
                "elapsed_ms": int((time.monotonic() - started) * 1000),
            })
        except Exception as exc:
            attempt.update({
                "request_ok": False,
                "stage": "http_request",
                "error": repr(exc),
                "elapsed_ms": int((time.monotonic() - started) * 1000),
            })
            attempts.append(attempt)
            continue

        page = r.text or ""
        if not (200 <= r.status_code < 400):
            attempt.update({"stage": "http_status", "message": f"TikTok HTTP {r.status_code}"})
            attempts.append(attempt)
            continue
        if not page.strip():
            attempt.update({"stage": "empty_html", "message": "TikTok stuurde lege HTML terug"})
            attempts.append(attempt)
            continue

        reason = challenge_reason(page)
        if reason:
            attempt.update({"stage": "challenge", "challenge": reason, "html_prefix": page[:180]})
            attempts.append(attempt)
            continue

        item, detected_scripts, parser_source = parse_item(page, video_id)
        attempt["detected_json_scripts"] = detected_scripts
        if item is None:
            attempt.update({
                "stage": "parse_hydration",
                "message": "Geen video-object met stats gevonden",
                "html_prefix": re.sub(r"\\s+", " ", page[:220]),
            })
            attempts.append(attempt)
            continue

        stats, author, title, thumb = normalize(item)
        attempt["parser_source"] = parser_source
        if all(v is None for v in stats.values()):
            attempt.update({"stage": "normalize_stats", "message": "Video-object gevonden maar counters ontbreken"})
            attempts.append(attempt)
            continue

        attempt["stage"] = "success"
        attempts.append(attempt)
        emit({
            "success": True,
            "stage": "success",
            "source": source,
            "stats": stats,
            "author_name": author,
            "title": title,
            "thumbnail_url": thumb,
            "http_status": int(r.status_code),
            "final_url": str(r.url),
            "debug": {
                "python": sys.executable,
                "python_version": sys.version.split()[0],
                "curl_cffi": getattr(__import__("curl_cffi"), "__version__", "unknown"),
                "attempts": attempts,
            },
        }, 0)

    emit({
        "success": False,
        "stage": attempts[-1].get("stage", "unknown") if attempts else "no_attempts",
        "message": "TikTok gaf geen bruikbare publieke data terug",
        "debug": {
            "python": sys.executable,
            "python_version": sys.version.split()[0],
            "curl_cffi": getattr(__import__("curl_cffi"), "__version__", "unknown"),
            "attempts": attempts,
        },
    }, 2)


if __name__ == "__main__":
    main()
