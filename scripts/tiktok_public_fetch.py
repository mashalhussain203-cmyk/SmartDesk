#!/usr/bin/env python3
import html as htmlmod
import json
import re
import sys
import time
from urllib.parse import urlsplit, urlunsplit, urlencode, parse_qsl
from urllib.request import Request, build_opener, HTTPCookieProcessor
from urllib.error import HTTPError, URLError
import http.cookiejar

try:
    import yt_dlp
    HAS_YTDLP = True
    YTDLP_ERROR = None
except Exception as exc:
    yt_dlp = None
    HAS_YTDLP = False
    YTDLP_ERROR = repr(exc)


def emit(obj, exit_code=0):
    print(json.dumps(obj, ensure_ascii=False, separators=(",", ":")))
    raise SystemExit(exit_code)


# curl_cffi is preferred, but the helper must not crash when Railway did not
# install it. In that case we still try a normal browser-like HTTPS request.
try:
    from curl_cffi import requests as curl_requests
    HAS_CURL_CFFI = True
    CURL_CFFI_ERROR = None
except Exception as exc:
    curl_requests = None
    HAS_CURL_CFFI = False
    CURL_CFFI_ERROR = repr(exc)


def add_buster(url):
    parts = urlsplit(url)
    q = dict(parse_qsl(parts.query, keep_blank_values=True))
    q["lang"] = "en"
    q["_mashal_live"] = str(time.time_ns())
    return urlunsplit((parts.scheme or "https", parts.netloc, parts.path, urlencode(q), parts.fragment))


def script_json(page, script_id):
    # IMPORTANT: use \b, not \\b. The previous build accidentally searched for
    # literal backslashes and therefore missed TikTok's hydration scripts.
    pattern = re.compile(
        r'<script\b[^>]*\bid=["\']' + re.escape(script_id) + r'["\'][^>]*>(.*?)</script>',
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
        k in stats for k in (
            "playCount", "play_count", "viewCount", "view_count",
            "diggCount", "digg_count", "likeCount", "like_count",
        )
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
        direct = node.get(str(video_id))
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
        if isinstance(v, dict):
            # statsV2 sometimes wraps numbers in {"value":"123"}
            v = v.get("value") or v.get("count")
        try:
            return int(str(v).replace(",", "").strip())
        except Exception:
            pass
    return None


def raw_counter(text, keys):
    # TikTok sometimes embeds counters in escaped JSON rather than a normal
    # hydration script. Search both normal and backslash-escaped key forms.
    variants = [text, htmlmod.unescape(text)]
    variants.append(variants[-1].replace('\\\"', '"').replace('\\u0022', '"'))

    for blob in variants:
        for key in keys:
            k = re.escape(key)
            patterns = (
                r'["\\\']' + k + r'["\\\']\\s*:\\s*["\\\']?([0-9]{1,20})',
                r'\\\\["\\\']' + k + r'\\\\["\\\']\\s*:\\s*\\\\?["\\\']?([0-9]{1,20})',
                r'\\b' + k + r'\\b\\s*[:=]\\s*["\\\']?([0-9]{1,20})',
            )
            for pattern in patterns:
                m = re.search(pattern, blob, re.I)
                if m:
                    try:
                        return int(m.group(1))
                    except Exception:
                        pass
    return None


def parse_raw_stats(page, video_id):
    # Prefer a window around the requested video id so another video embedded
    # in the page cannot accidentally win.
    decoded = htmlmod.unescape(page)
    decoded = decoded.replace('\\\"', '"').replace('\\u0022', '"')

    windows = []
    for blob in (page, decoded):
        pos = blob.find(str(video_id))
        if pos >= 0:
            windows.append(blob[max(0, pos - 90000):pos + 180000])
        windows.append(blob)

    for blob in windows:
        stats = {
            "playCount": raw_counter(blob, ("playCount", "play_count", "viewCount", "view_count")),
            "diggCount": raw_counter(blob, ("diggCount", "digg_count", "likeCount", "like_count")),
            "commentCount": raw_counter(blob, ("commentCount", "comment_count")),
            "shareCount": raw_counter(blob, ("shareCount", "share_count")),
        }

        found = [v for v in stats.values() if v is not None]
        if len(found) >= 2 and stats["playCount"] is not None:
            return {
                "id": str(video_id),
                "stats": stats,
            }
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
        if not isinstance(data, dict):
            continue
        detected_scripts.append(sid)

        if sid == "SIGI_STATE":
            direct = (data.get("ItemModule") or {}).get(str(video_id))
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
    for raw in re.findall(r'<script\b[^>]*type=["\']application/json["\'][^>]*>(.*?)</script>', page, re.I | re.S):
        app_json_count += 1
        try:
            data = json.loads(htmlmod.unescape(raw).strip())
        except Exception:
            continue
        found = recursive_find(data, video_id)
        if found is not None:
            return found, detected_scripts + [f"application/json:{app_json_count}"], "application/json"

    if app_json_count:
        detected_scripts.append(f"application/json_count={app_json_count}")

    raw_item = parse_raw_stats(page, video_id)
    if raw_item is not None:
        detected_scripts.append("raw_html_stats")
        return raw_item, detected_scripts, "raw_html_stats"

    return None, detected_scripts, None


def challenge_reason(page):
    sample = page[:350000].lower()
    for needle in (
        "secsdk-captcha", "verify to continue", "verify you are human",
        "security verification", "captcha", "access denied", "robot check",
    ):
        if needle in sample:
            return needle
    return None


COMMON_HEADERS = {
    "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36",
    "Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8",
    "Accept-Language": "en-US,en;q=0.9,nl;q=0.7",
    "Cache-Control": "no-cache",
    "Pragma": "no-cache",
    "Referer": "https://www.tiktok.com/",
    "Upgrade-Insecure-Requests": "1",
}


def fetch_with_curl_cffi(url):
    started = time.monotonic()
    r = curl_requests.get(
        url,
        headers=COMMON_HEADERS,
        timeout=15,
        allow_redirects=True,
        impersonate="chrome",
    )
    return {
        "status": int(r.status_code),
        "url": str(r.url),
        "content_type": r.headers.get("content-type"),
        "text": r.text or "",
        "bytes": len(r.content or b""),
        "elapsed_ms": int((time.monotonic() - started) * 1000),
        "transport": "curl_cffi",
    }


def fetch_with_stdlib(url):
    started = time.monotonic()
    jar = http.cookiejar.CookieJar()
    opener = build_opener(HTTPCookieProcessor(jar))
    req = Request(url, headers=COMMON_HEADERS, method="GET")
    try:
        with opener.open(req, timeout=15) as r:
            raw = r.read()
            charset = r.headers.get_content_charset() or "utf-8"
            text = raw.decode(charset, errors="replace")
            return {
                "status": int(getattr(r, "status", 200)),
                "url": str(r.geturl()),
                "content_type": r.headers.get("content-type"),
                "text": text,
                "bytes": len(raw),
                "elapsed_ms": int((time.monotonic() - started) * 1000),
                "transport": "urllib",
            }
    except HTTPError as e:
        raw = e.read() if hasattr(e, "read") else b""
        return {
            "status": int(e.code),
            "url": str(e.geturl() if hasattr(e, "geturl") else url),
            "content_type": e.headers.get("content-type") if e.headers else None,
            "text": raw.decode("utf-8", errors="replace"),
            "bytes": len(raw),
            "elapsed_ms": int((time.monotonic() - started) * 1000),
            "transport": "urllib",
        }


def fetch(url):
    errors = []
    if HAS_CURL_CFFI:
        try:
            return fetch_with_curl_cffi(url), errors
        except Exception as exc:
            errors.append("curl_cffi: " + repr(exc))
    try:
        return fetch_with_stdlib(url), errors
    except (URLError, OSError, Exception) as exc:
        errors.append("urllib: " + repr(exc))
        return None, errors


def fetch_with_ytdlp(video_url, video_id):
    if not HAS_YTDLP:
        return None, {"stage": "ytdlp_import", "message": YTDLP_ERROR or "yt-dlp niet beschikbaar"}

    opts = {
        "quiet": True,
        "no_warnings": True,
        "skip_download": True,
        "noplaylist": True,
        "socket_timeout": 18,
        "retries": 1,
        "extractor_retries": 1,
        "http_headers": COMMON_HEADERS,
    }

    try:
        with yt_dlp.YoutubeDL(opts) as ydl:
            info = ydl.extract_info(video_url, download=False)
    except Exception as exc:
        return None, {"stage": "ytdlp_extract", "message": repr(exc)}

    if not isinstance(info, dict):
        return None, {"stage": "ytdlp_empty", "message": "yt-dlp gaf geen video-object terug"}

    found_id = str(info.get("id") or "")
    if found_id and found_id != str(video_id):
        return None, {"stage": "ytdlp_wrong_video", "found_id": found_id}

    stats = {
        "views": first_int(info.get("view_count")),
        "likes": first_int(info.get("like_count")),
        "comments": first_int(info.get("comment_count")),
        "shares": first_int(info.get("repost_count"), info.get("share_count")),
    }

    if stats["views"] is None and stats["likes"] is None and stats["comments"] is None and stats["shares"] is None:
        return None, {
            "stage": "ytdlp_no_stats",
            "available_keys": sorted([k for k in info.keys() if isinstance(k, str)])[:120],
        }

    return {
        "success": True,
        "stage": "success",
        "source": "yt-dlp",
        "precision": "raw_integer",
        "stats": stats,
        "author_name": info.get("uploader_id") or info.get("channel_id") or info.get("uploader"),
        "title": info.get("description") or info.get("title"),
        "thumbnail_url": info.get("thumbnail"),
        "debug": {
            "yt_dlp_available": True,
            "extractor": info.get("extractor"),
            "extractor_key": info.get("extractor_key"),
        },
    }, None


def main():
    if len(sys.argv) < 3:
        emit({"success": False, "stage": "arguments", "message": "Gebruik: tiktok_public_fetch.py <url> <video_id>"}, 2)

    video_url = sys.argv[1]
    video_id = str(sys.argv[2])
    targets = [
        ("public_video_page", add_buster(video_url)),
        ("public_embed_v2", f"https://www.tiktok.com/embed/v2/{video_id}?lang=en-US&_mashal_live={time.time_ns()}"),
    ]
    attempts = []

    # Prefer yt-dlp first because it maps TikTok's raw stats object to integer
    # view_count/like_count/comment_count/repost_count. The public HTML can
    # sometimes contain presentation-rounded counters (e.g. 11.1M).
    ytdlp_result, ytdlp_debug = fetch_with_ytdlp(video_url, video_id)
    attempts.append({
        "source": "yt-dlp",
        **(ytdlp_debug or {"stage": "success"}),
    })
    if isinstance(ytdlp_result, dict) and ytdlp_result.get("success") is True:
        ytdlp_result.setdefault("debug", {})["previous_attempts"] = []
        emit(ytdlp_result)

    for source, target in targets:
        attempt = {"source": source, "target": target}
        result, transport_errors = fetch(target)
        if transport_errors:
            attempt["transport_errors"] = transport_errors

        if result is None:
            attempt.update({"stage": "http_request", "message": "Alle HTTP transports faalden"})
            attempts.append(attempt)
            continue

        page = result.pop("text")
        attempt.update({
            "request_ok": True,
            "http_status": result["status"],
            "final_url": result["url"],
            "content_type": result["content_type"],
            "content_length": result["bytes"],
            "elapsed_ms": result["elapsed_ms"],
            "transport": result["transport"],
        })

        if not (200 <= result["status"] < 400):
            attempt.update({"stage": "http_status", "message": f"TikTok HTTP {result['status']}"})
            attempts.append(attempt)
            continue
        if not page.strip():
            attempt.update({"stage": "empty_html", "message": "TikTok stuurde lege HTML terug"})
            attempts.append(attempt)
            continue

        reason = challenge_reason(page)
        if reason:
            attempt.update({"stage": "challenge", "challenge": reason, "html_prefix": re.sub(r"\s+", " ", page[:180])})
            attempts.append(attempt)
            continue

        item, detected_scripts, parser_source = parse_item(page, video_id)
        attempt["detected_json_scripts"] = detected_scripts
        if item is None:
            attempt.update({
                "stage": "parse_hydration",
                "message": "Geen video-object met stats gevonden",
                "html_prefix": re.sub(r"\s+", " ", page[:220]),
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
            "http_status": result["status"],
            "final_url": result["url"],
            "debug": {
                "python": sys.executable,
                "python_version": sys.version.split()[0],
                "curl_cffi_available": HAS_CURL_CFFI,
                "curl_cffi_import_error": CURL_CFFI_ERROR,
                "attempts": attempts,
            },
        })

    # Important: return JSON on stdout. Exit 10 makes Laravel's debug controller
    # surface a 502 with detailed diagnostics instead of pretending it succeeded.
    emit({
        "success": False,
        "stage": attempts[-1].get("stage", "all_sources_failed") if attempts else "all_sources_failed",
        "message": attempts[-1].get("message", "Geen publieke TikTok-bron leverde bruikbare stats") if attempts else "Geen publieke TikTok-bron leverde bruikbare stats",
        "debug": {
            "python": sys.executable,
            "python_version": sys.version.split()[0],
            "curl_cffi_available": HAS_CURL_CFFI,
            "curl_cffi_import_error": CURL_CFFI_ERROR,
            "yt_dlp_available": HAS_YTDLP,
            "yt_dlp_import_error": YTDLP_ERROR,
            "attempts": attempts,
        },
    }, 10)


if __name__ == "__main__":
    main()
