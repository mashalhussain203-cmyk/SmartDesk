#!/usr/bin/env python3
"""Extra public-only TikTok profile list fallback for Best Upload Times.

No login, no cookies, no invented statistics. yt-dlp may return no videos if
TikTok blocks the hosting IP; that is reported as a source error, not 0 views.
"""
import json
import re
import sys
import time


def emit(**data):
    print(json.dumps(data, ensure_ascii=False, separators=(",", ":")))


def as_int(value):
    if type(value) in (int, float):
        return int(value) if value >= 0 else None
    if isinstance(value, str) and value.isdigit():
        return int(value)
    return None


def main():
    if len(sys.argv) != 2 or not re.fullmatch(r"[A-Za-z0-9._]{1,24}", sys.argv[1]):
        emit(success=False, error="Ongeldige gebruikersnaam")
        return

    username = sys.argv[1]
    try:
        import yt_dlp
    except ImportError:
        emit(success=False, error="yt-dlp niet geïnstalleerd")
        return

    options = {
        "quiet": True,
        "no_warnings": True,
        "skip_download": True,
        "noplaylist": False,
        "playlistend": 35,
        "cachedir": False,
        "socket_timeout": 5,
        "retries": 0,
        "extractor_retries": 0,
        "ignoreerrors": True,
        "extract_flat": "in_playlist",
    }

    try:
        with yt_dlp.YoutubeDL(options) as ydl:
            result = ydl.extract_info(
                "https://www.tiktok.com/@" + username, download=False
            )
    except Exception as exc:
        emit(success=False, error=type(exc).__name__, posts=[])
        return

    if not isinstance(result, dict):
        emit(success=False, error="Profiel niet beschikbaar", posts=[])
        return

    posts = {}
    for item in (result.get("entries") or []):
        if not isinstance(item, dict):
            continue

        video_id = str(item.get("id") or "")
        if not re.fullmatch(r"\d{15,22}", video_id):
            continue

        uploader = str(item.get("uploader") or item.get("channel") or "")
        if uploader and uploader.lower().lstrip("@") != username.lower():
            continue

        views = as_int(item.get("view_count"))
        when = as_int(item.get("timestamp"))
        if when is None:
            when = int(video_id) >> 32
        if views is None or when is None or not 1577836800 < when < time.time() + 86400:
            continue
        posts[video_id] = {
            "id": video_id, "views": views, "published_at": when
        }

    emit(success=bool(posts), source="yt-dlp public TikTok user feed",
         posts=list(posts.values()), error=None if posts else "Geen publieke videostatistieken")


if __name__ == "__main__":
    main()
