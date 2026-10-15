#!/usr/bin/env python3
"""Read public metadata of one TikTok video (never download the video).

Output a small JSON object, never raw extractor data or credentials.
This is a fallback when TikTok's public HTML omits codec/FPS/size.
"""
import json
import re
import sys
from urllib.parse import urlsplit


def main():
    if len(sys.argv) != 3:
        print(json.dumps({"success": False, "message": "Invalid arguments"}))
        return
    url, wanted_id = sys.argv[1:]
    parsed = urlsplit(url)
    if parsed.scheme != "https" or parsed.hostname not in {
        "www.tiktok.com", "tiktok.com", "m.tiktok.com"
    } or not re.fullmatch(r"\d{10,30}", wanted_id):
        print(json.dumps({"success": False, "message": "Unsupported TikTok URL"}))
        return

    try:
        import yt_dlp
    except ImportError:
        print(json.dumps({"success": False, "message": "yt-dlp is unavailable"}))
        return

    opts = {
        "quiet": True,
        "no_warnings": True,
        "skip_download": True,
        "noplaylist": True,
        "cachedir": False,
        "socket_timeout": 9,
        "retries": 1,
        "extractor_retries": 1,
    }
    try:
        with yt_dlp.YoutubeDL(opts) as ydl:
            info = ydl.extract_info(url, download=False)
    except Exception:
        print(json.dumps({"success": False, "message": "Public extractor unavailable"}))
        return

    if not isinstance(info, dict) or str(info.get("id")) != wanted_id:
        print(json.dumps({"success": False, "message": "Video ID mismatch"}))
        return

    def number(value):
        if isinstance(value, (int, float)) and not isinstance(value, bool) and value >= 0:
            return value
        return None

    video_formats = [
        f for f in (info.get("formats") or []) if isinstance(f, dict)
        and f.get("vcodec") not in ("none", None)
        and number(f.get("height")) is not None
    ]
    video_formats.sort(
        key=lambda f: (
            number(f.get("height")) or 0,
            number(f.get("width")) or 0,
            number(f.get("fps")) or 0,
            number(f.get("tbr")) or 0,
        ),
        reverse=True,
    )
    selected = video_formats[0] if video_formats else {}
    width = number(selected.get("width")) or number(info.get("width"))
    height = number(selected.get("height")) or number(info.get("height"))
    fps = number(selected.get("fps")) or number(info.get("fps"))
    size = number(selected.get("filesize")) or number(selected.get("filesize_approx")) or number(info.get("filesize"))
    tbr = number(selected.get("tbr")) or number(info.get("tbr"))
    codec = selected.get("vcodec") or info.get("vcodec")
    direct_url = selected.get("url")

    result = {
        "success": True, "source": "yt-dlp public TikTok metadata",
        "width": width, "height": height, "fps": fps,
        "bitrate": round(tbr * 1000) if tbr else None,
        "file_size": size,
        "codec": codec if isinstance(codec, str) else None,
        "duration": number(info.get("duration")),
        "published_at": number(info.get("timestamp")) or number(info.get("release_timestamp")),
        "description": info.get("description") if isinstance(info.get("description"), str) else None,
        "author": info.get("uploader_id") or info.get("channel_id"),
        "tags": info.get("tags") if isinstance(info.get("tags"), list) else [],
        "direct_url": direct_url if isinstance(direct_url, str) and direct_url.startswith("https://") else None,
    }
    print(json.dumps(result, ensure_ascii=False))


if __name__ == "__main__":
    main()
