#!/usr/bin/env python3
"""Extract public video metadata without downloading the full video.

Try ffprobe on a validated CDN stream before yt-dlp. ffprobe only reads
container/stream headers; it does not transcode or download a complete MP4.
All results are from real metadata, never guessed from resolution/bitrate.
"""
import json
import math
import re
import shutil
import subprocess
import sys
from urllib.parse import urlsplit


CDN_SUFFIXES = (
    "tiktokcdn.com", "tiktokv.com", "tikwm.com", "akamaized.net",
    "byteoversea.com", "ibytedtos.com",
)


def emit(result):
    print(json.dumps(result, ensure_ascii=False, separators=(",", ":")))


def safe_media_url(value):
    if not isinstance(value, str) or len(value) > 3000:
        return False
    try:
        parts = urlsplit(value)
        if parts.scheme != "https" or not parts.hostname or parts.username or parts.password:
            return False
        host = parts.hostname.lower().rstrip(".")
        return any(host == suffix or host.endswith("." + suffix) for suffix in CDN_SUFFIXES)
    except (ValueError, TypeError):
        return False


def number(value):
    if isinstance(value, str) and value.strip():
        try:
            value = float(value)
        except ValueError:
            return None
    if isinstance(value, (float, int)) and not isinstance(value, bool) and math.isfinite(value) and value >= 0:
        return value
    return None


def frame_rate(value):
    if isinstance(value, (float, int)):
        rate = float(value)
    elif isinstance(value, str):
        try:
            if "/" in value:
                numerator, denominator = value.split("/", 1)
                den = float(denominator)
                rate = float(numerator) / den if den else 0.0
            else:
                rate = float(value)
        except (ValueError, ZeroDivisionError):
            return None
    else:
        return None
    if not math.isfinite(rate) or rate < 1 or rate > 240:
        return None
    return int(round(rate))


def ffprobe_media(url):
    if not safe_media_url(url) or not shutil.which("ffprobe"):
        return {}
    args = [
        "ffprobe", "-v", "error",
        "-rw_timeout", "5000000",
        "-probesize", "2000000", "-analyzeduration", "1000000",
        "-select_streams", "v:0",
        "-show_entries", "stream=avg_frame_rate,r_frame_rate,width,height,codec_name,bit_rate:format=duration,size,bit_rate",
        "-of", "json", url,
    ]
    try:
        process = subprocess.run(
            args, capture_output=True, text=True, timeout=9, check=False
        )
        if process.returncode != 0 or len(process.stdout) > 50000:
            return {}
        payload = json.loads(process.stdout)
    except (OSError, ValueError, subprocess.TimeoutExpired):
        return {}

    streams = payload.get("streams", [])
    if not isinstance(streams, list) or not streams or not isinstance(streams[0], dict):
        return {}
    stream = streams[0]
    fmt = payload.get("format", {}) if isinstance(payload.get("format"), dict) else {}
    fps = frame_rate(stream.get("avg_frame_rate")) or frame_rate(stream.get("r_frame_rate"))
    return {
        "width": number(stream.get("width")),
        "height": number(stream.get("height")),
        "fps": fps,
        "bitrate": number(stream.get("bit_rate")) or number(fmt.get("bit_rate")),
        "file_size": number(fmt.get("size")),
        "codec": stream.get("codec_name"),
        "duration": number(fmt.get("duration")),
    }


def youtube_dl_info(url, wanted_id):
    try:
        import yt_dlp
    except ImportError:
        return None
    opts = {
        "quiet": True,
        "no_warnings": True,
        "skip_download": True,
        "noplaylist": True,
        "cachedir": False,
        "socket_timeout": 7,
        "retries": 1,
        "extractor_retries": 1,
    }
    try:
        with yt_dlp.YoutubeDL(opts) as ydl:
            info = ydl.extract_info(url, download=False)
    except Exception:
        return None
    if not isinstance(info, dict) or str(info.get("id") or "") != wanted_id:
        return None
    return info


def extract_ytdlp(info):
    if not info:
        return {}
    formats = [
        f for f in info.get("formats") or []
        if isinstance(f, dict)
        and f.get("vcodec") not in ("none", None)
        and number(f.get("height")) is not None
    ]
    formats.sort(
        key=lambda f: (
            number(f.get("height")) or 0,
            number(f.get("width")) or 0,
            number(f.get("fps")) or 0,
            number(f.get("tbr")) or 0,
        ), reverse=True,
    )
    selected = formats[0] if formats else {}
    tbr = number(selected.get("tbr")) or number(info.get("tbr"))
    direct_url = selected.get("url")
    return {
        "width": number(selected.get("width")) or number(info.get("width")),
        "height": number(selected.get("height")) or number(info.get("height")),
        "fps": frame_rate(selected.get("fps")) or frame_rate(info.get("fps")),
        "bitrate": round(tbr * 1000) if tbr else None,
        "file_size": number(selected.get("filesize"))
                    or number(selected.get("filesize_approx"))
                    or number(info.get("filesize")),
        "codec": selected.get("vcodec") or info.get("vcodec"),
        "duration": number(info.get("duration")),
        "published_at": number(info.get("timestamp")) or number(info.get("release_timestamp")),
        "description": info.get("description") if isinstance(info.get("description"), str) else None,
        "author": info.get("uploader_id") or info.get("channel_id"),
        "tags": info.get("tags") if isinstance(info.get("tags"), list) else [],
        "direct_url": direct_url if safe_media_url(direct_url) else None,
    }


def main():
    if len(sys.argv) not in (3, 4):
        emit({"success": False, "message": "Invalid arguments"})
        return
    url, wanted_id = sys.argv[1:3]
    parsed = urlsplit(url)
    if parsed.scheme != "https" or parsed.hostname not in {
        "www.tiktok.com", "tiktok.com", "m.tiktok.com"
    } or not re.fullmatch(r"\d{10,30}", wanted_id):
        emit({"success": False, "message": "Unsupported TikTok URL"})
        return

    media_url = sys.argv[3] if len(sys.argv) == 4 else None
    direct = ffprobe_media(media_url)
    # Prefer probing the actual video stream: yt-dlp's generic format often
    # does not carry FPS even though the downloaded container does.
    if direct.get("fps") is not None:
        emit({"success": True, "source": "ffprobe public video headers", **direct})
        return

    info = youtube_dl_info(url, wanted_id)
    extracted = extract_ytdlp(info)
    # If TikWM did not supply a compatible CDN URL, try the validated direct
    # stream returned by TikTok's public yt-dlp extractor.
    secondary = ffprobe_media(extracted.get("direct_url")) if extracted.get("direct_url") != media_url else {}
    result = {
        key: direct.get(key) or secondary.get(key) or extracted.get(key)
        for key in (
            "width", "height", "fps", "bitrate", "file_size", "codec", "duration",
            "published_at", "description", "author", "tags", "direct_url",
        )
    }
    if result.get("fps") is None and not extracted and not direct:
        emit({"success": False, "message": "No public video metadata available"})
        return
    emit({"success": True, "source": "ffprobe + yt-dlp public video metadata", **result})


if __name__ == "__main__":
    main()
