#!/usr/bin/env python3
"""Best-effort public TikTok video metadata.

Uses TikTok's own public extractor and ffprobe against validated video CDN URLs.
No credentials, no fabricated values, no full-video download.
"""
import json
import math
import re
import shutil
import subprocess
import sys
from urllib.parse import urlsplit


CDN_SUFFIXES = (
    "tiktokcdn.com", "tiktokcdn-us.com", "tiktokcdn-eu.com",
    "tiktokcdn-in.com", "tiktokv.com", "tiktokv.us", "tikwm.com",
    "akamaized.net", "byteoversea.com", "ibytedtos.com",
    "tiktok.com", "muscdn.com",
)


def emit(value):
    print(json.dumps(value, ensure_ascii=False, separators=(",", ":")))


def safe_media_url(value):
    if not isinstance(value, str) or len(value) > 4000:
        return False
    try:
        parsed = urlsplit(value)
        if parsed.scheme != "https" or not parsed.hostname or parsed.username or parsed.password:
            return False
        hostname = parsed.hostname.lower().rstrip(".")
        return any(hostname == suffix or hostname.endswith("." + suffix) for suffix in CDN_SUFFIXES)
    except ValueError:
        return False


def number(value):
    if isinstance(value, str):
        try:
            value = float(value)
        except ValueError:
            return None
    if isinstance(value, (int, float)) and not isinstance(value, bool):
        if math.isfinite(value) and value >= 0:
            return value
    return None


def rate(value):
    if isinstance(value, str) and "/" in value:
        try:
            a, b = value.split("/", 1)
            value = float(a) / float(b)
        except (ValueError, ZeroDivisionError):
            return None
    value = number(value)
    if value is not None and 1 <= value <= 240:
        return round(value, 3)
    return None


def stream_probe(media_url):
    """Ask ffprobe for MP4/HLS headers, without saving the entire media."""
    if not shutil.which("ffprobe") or not safe_media_url(media_url):
        return {}
    args = [
        "ffprobe", "-v", "error", "-rw_timeout", "4000000",
        "-user_agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/130.0.0.0 Safari/537.36",
        "-headers", "Referer: https://www.tiktok.com/\r\n",
        "-probesize", "800000",
        "-analyzeduration", "700000",
        "-select_streams", "v:0",
        "-show_entries", "stream=avg_frame_rate,r_frame_rate,width,height,codec_name,bit_rate:format=duration,size,bit_rate",
        "-of", "json", media_url,
    ]
    try:
        proc = subprocess.run(args, capture_output=True, text=True, timeout=6, check=False)
        if proc.returncode != 0 or len(proc.stdout) > 60000:
            return {}
        data = json.loads(proc.stdout)
    except (OSError, ValueError, subprocess.TimeoutExpired):
        return {}
    streams = data.get("streams", [])
    if not streams or not isinstance(streams[0], dict):
        return {}
    stream = streams[0]
    fmt = data.get("format") if isinstance(data.get("format"), dict) else {}
    return {
        "fps": rate(stream.get("avg_frame_rate")) or rate(stream.get("r_frame_rate")),
        "width": number(stream.get("width")),
        "height": number(stream.get("height")),
        "codec": stream.get("codec_name"),
        "bitrate": number(stream.get("bit_rate")) or number(fmt.get("bit_rate")),
        "file_size": number(fmt.get("size")),
        "duration": number(fmt.get("duration")),
        "source": "ffprobe (MP4-stream)",
    }


def partial_download_probe(media_url):
    """Fallback: read at most the first 5 MiB of a CDN file with byte ranges."""
    if not safe_media_url(media_url) or not shutil.which("ffprobe"):
        return {}
    try:
        from curl_cffi import requests
    except ImportError:
        return {}
    import tempfile
    import os
    name = None
    try:
        response = requests.get(
            media_url,
            impersonate="chrome",
            headers={
                "Range": "bytes=0-5242879",
                "Referer": "https://www.tiktok.com/",
                "Accept": "*/*",
            },
            timeout=7,
            allow_redirects=True,
            stream=True,
        )
        if not safe_media_url(response.url) or response.status_code != 206:
            return {}
        total = 0
        with tempfile.NamedTemporaryFile(suffix=".mp4", delete=False) as temp:
            name = temp.name
            for chunk in response.iter_content(chunk_size=65536):
                total += len(chunk)
                if total > 5242880:
                    return {}
                temp.write(chunk)
        args = [
            "ffprobe", "-v", "error", "-probesize", "1000000",
            "-analyzeduration", "700000", "-select_streams", "v:0",
            "-show_entries", "stream=avg_frame_rate,r_frame_rate,width,height,codec_name,bit_rate",
            "-of", "json", name,
        ]
        completed = subprocess.run(args, capture_output=True, text=True, timeout=5)
        if completed.returncode != 0:
            return {}
        result = json.loads(completed.stdout)
        streams = result.get("streams", [])
        if not streams or not isinstance(streams[0], dict):
            return {}
        stream = streams[0]
        return {
            "fps": rate(stream.get("avg_frame_rate")) or rate(stream.get("r_frame_rate")),
            "width": number(stream.get("width")),
            "height": number(stream.get("height")),
            "codec": stream.get("codec_name"),
            "bitrate": number(stream.get("bit_rate")),
            "source": "ffprobe (eerste 5 MB van MP4)",
        }
    except Exception:
        return {}
    finally:
        if name:
            try:
                os.unlink(name)
            except OSError:
                pass


def ytdlp_info(public_url, video_id):
    try:
        import yt_dlp
    except ImportError:
        return {}
    try:
        opts = {
            "quiet": True, "no_warnings": True,
            "skip_download": True, "noplaylist": True,
            "cachedir": False, "socket_timeout": 6,
            "retries": 0, "extractor_retries": 0,
        }
        with yt_dlp.YoutubeDL(opts) as ydl:
            result = ydl.extract_info(public_url, download=False)
        return result if isinstance(result, dict) and str(result.get("id")) == video_id else {}
    except Exception:
        return {}


def main():
    if len(sys.argv) < 3 or len(sys.argv) > 5:
        emit({"success": False, "reason": "arguments"})
        return
    url, video_id = sys.argv[1:3]
    parsed = urlsplit(url)
    if parsed.scheme != "https" or parsed.hostname not in {
        "www.tiktok.com", "tiktok.com", "m.tiktok.com"
    } or not re.fullmatch(r"\d{10,30}", video_id):
        emit({"success": False, "reason": "invalid_video_url"})
        return

    media = []
    for item in sys.argv[3:]:
        if safe_media_url(item) and item not in media:
            media.append(item)

    result = {}
    info = {}
    # Prefer the already-known public CDN stream to avoid slow extraction.
    for candidate in media[:2]:
        result = stream_probe(candidate)
        if result.get("fps"):
            break

    if not result.get("fps"):
        info = ytdlp_info(url, video_id)
        formats = info.get("formats") or []
        usable = [
            x for x in formats
            if isinstance(x, dict) and x.get("vcodec") not in (None, "none")
        ]
        usable.sort(
            key=lambda f: (number(f.get("height")) or 0, number(f.get("tbr")) or 0),
            reverse=True
        )
        if usable:
            selected = usable[0]
            metadata = {
                "fps": rate(selected.get("fps")) or rate(info.get("fps")),
                "width": number(selected.get("width")) or number(info.get("width")),
                "height": number(selected.get("height")) or number(info.get("height")),
                "codec": selected.get("vcodec") or info.get("vcodec"),
                "bitrate": round((number(selected.get("tbr")) or 0) * 1000) or None,
                "file_size": number(selected.get("filesize")) or number(selected.get("filesize_approx")),
                "duration": number(info.get("duration")),
                "source": "yt-dlp openbare TikTok-metadata",
            }
            result = {key: result.get(key) or val for key, val in metadata.items()}
            stream_url = selected.get("url")
            if not result.get("fps") and safe_media_url(stream_url):
                probe = stream_probe(stream_url)
                if not probe.get("fps"):
                    probe = partial_download_probe(stream_url)
                if probe.get("fps"):
                    result = {key: result.get(key) or val for key, val in probe.items()}
                    result["source"] = probe.get("source")
        elif info:
            result["fps"] = rate(info.get("fps"))
            result["source"] = "yt-dlp openbare TikTok-metadata"

    # Bounded partial fetch of the best media source for opaque CDN responses.
    if not result.get("fps") and media:
        probe = partial_download_probe(media[0])
        if probe.get("fps"):
            result = {key: result.get(key) or val for key, val in probe.items()}
            result["source"] = probe.get("source")

    # Return partial fields (fps may still be unavailable).
    result["success"] = bool(result) or bool(info)
    result["fps_status"] = (
        "measured" if result.get("fps") is not None
        else "unavailable_in_public_media"
    )
    emit(result)


if __name__ == "__main__":
    main()
