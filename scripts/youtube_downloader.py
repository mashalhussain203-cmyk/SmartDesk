#!/usr/bin/env python3
"""Public YouTube media analyser/downloader for Mashal Studio.
Uses yt-dlp and FFmpeg; does not use login cookies, DRM circumvention, or private-video access.
"""
import json
import os
import re
import shutil
import subprocess
import sys
import time
from pathlib import Path
from urllib.parse import urlsplit

MAX_BYTES = 520 * 1024 * 1024
ALLOWED_HOSTS = {"youtube.com", "www.youtube.com", "m.youtube.com", "music.youtube.com", "youtu.be", "www.youtube-nocookie.com"}


def emit(data):
    print(json.dumps(data, ensure_ascii=False), flush=True)


def valid_url(value):
    try:
        p = urlsplit(value)
        if p.scheme != "https" or p.hostname not in ALLOWED_HOSTS or p.username or p.password or p.port:
            return False
        return bool(re.fullmatch(r"https://www\.youtube\.com/watch\?v=[A-Za-z0-9_-]{11}", value))
    except ValueError:
        return False


def write_status(directory, status, **extra):
    data = {"status": status, "updated_at": int(time.time()), **extra}
    temp = directory / "status.json.tmp"
    temp.write_text(json.dumps(data, ensure_ascii=False), encoding="utf-8")
    os.replace(temp, directory / "status.json")


def extract_info(url):
    import yt_dlp
    opts = {
        "quiet": True, "no_warnings": True, "noplaylist": True,
        "skip_download": True, "cachedir": False, "socket_timeout": 20,
        "retries": 1, "extractor_retries": 1, "ignoreerrors": False,
    }
    with yt_dlp.YoutubeDL(opts) as ydl:
        info = ydl.extract_info(url, download=False)
    if not isinstance(info, dict):
        raise RuntimeError("YouTube gaf geen videogegevens terug.")
    return info


def video_heights(info):
    heights = set()
    has_audio = False
    for fmt in info.get("formats") or []:
        if not isinstance(fmt, dict):
            continue
        if fmt.get("acodec") not in (None, "none"):
            has_audio = True
        if fmt.get("vcodec") not in (None, "none"):
            try:
                h = int(fmt.get("height") or 0)
                if h > 0:
                    heights.add(h)
            except (TypeError, ValueError):
                pass
    if not has_audio and info.get("acodec") not in (None, "none"):
        has_audio = True
    return sorted(heights, reverse=True), has_audio


def download(url, mode, quality, directory):
    import yt_dlp
    output = str(directory / "source.%(ext)s")
    if mode == "mp3" and not shutil.which("ffmpeg"):
        raise RuntimeError("MP3-conversie vereist FFmpeg, dat niet beschikbaar is.")

    # YouTube can reject one player client's signed media URLs with HTTP 403.
    # Re-extract formats with alternate public clients and fall back to the
    # best stream at or below the selected resolution.
    if mode == "mp3":
        formats = ["bestaudio/best"]
    else:
        height = int(quality)
        formats = [
            f"bestvideo[height={height}]+bestaudio/best[height={height}]",
            f"best[height={height}]",
            f"bestvideo[height<={height}]+bestaudio/best[height<={height}]",
            f"best[height<={height}]/best",
        ]

    # Prefer clients that do not require the same web PO-token flow.
    # Deno is installed in the Railway image to let yt-dlp solve supported JS challenges.
    client_sets = [
        ["tv"],
        ["web_safari"],
        ["android"],
    ]
    last_error = None
    succeeded = False

    for clients in client_sets:
        for format_selector in formats:
            for old in directory.glob("source.*"):
                old.unlink(missing_ok=True)
            common = {
                "quiet": True, "no_warnings": True, "noplaylist": True,
                "cachedir": False, "socket_timeout": 45, "retries": 3,
                "fragment_retries": 5, "extractor_retries": 3,
                "file_access_retries": 3, "outtmpl": output,
                "max_filesize": MAX_BYTES, "overwrites": True,
                "continuedl": False,
                "extractor_args": {"youtube": {"player_client": clients}},
                "format": format_selector,
            }
            if mode == "mp3":
                common["postprocessors"] = [{
                    "key": "FFmpegExtractAudio",
                    "preferredcodec": "mp3",
                    "preferredquality": "192",
                }]
            else:
                common["merge_output_format"] = "mp4"
                common["postprocessors"] = [{
                    "key": "FFmpegVideoRemuxer",
                    "preferedformat": "mp4",
                }]
            try:
                with yt_dlp.YoutubeDL(common) as ydl:
                    ydl.download([url])
                succeeded = True
                break
            except Exception as exc:
                last_error = exc
                message = (repr(exc) + " " + str(exc)).lower()
                # A 403 is commonly specific to a player client or format;
                # try the next public client/format before giving up.
                bot_challenge = ("sign in to confirm" in message or "not a bot" in message or "confirm you’re not a bot" in message)
                if "403" not in message and "forbidden" not in message and not bot_challenge:
                    raise
                time.sleep(2)
        if succeeded:
            break

    if not succeeded:
        detail = (str(last_error) if last_error else "").lower()
        if "sign in to confirm" in detail or "not a bot" in detail:
            reason = "YouTube blokkeert momenteel downloads vanaf de Railway-server met een botcontrole. De video is wel gevonden, maar YouTube geeft geen downloadstream vrij. Probeer later opnieuw; dit is een beperking aan YouTube-kant."
        elif "403" in detail or "forbidden" in detail:
            reason = "YouTube weigert de mediastream (HTTP 403) vanaf de downloadserver. Probeer later opnieuw of een lagere kwaliteit."
        else:
            reason = "YouTube heeft geen downloadbare mediastream teruggegeven. Probeer later opnieuw."
        raise RuntimeError(reason) from last_error

    extension = "mp3" if mode == "mp3" else "mp4"
    candidates = list(directory.glob("source." + extension))
    if not candidates and mode == "mp4":
        candidates = [p for p in directory.glob("source.*") if p.suffix.lower() in {".mkv", ".webm", ".mov"}]
        if candidates:
            source = candidates[0]
            target = directory / "download.mp4"
            result = subprocess.run([
                "ffmpeg", "-y", "-i", str(source), "-map", "0:v:0", "-map", "0:a:0?",
                "-c:v", "libx264", "-preset", "veryfast", "-crf", "21",
                "-c:a", "aac", "-b:a", "192k", "-movflags", "+faststart", str(target)
            ], capture_output=True, timeout=600)
            if result.returncode != 0:
                raise RuntimeError("YouTube-stream kon niet naar MP4 worden omgezet.")
            source.unlink(missing_ok=True)
            candidates = [target]
    if not candidates:
        raise RuntimeError("YouTube heeft het gevraagde MP4/MP3-bestand niet geleverd.")
    source = candidates[0]
    if source.stat().st_size < 1024 or source.stat().st_size > MAX_BYTES:
        raise RuntimeError("Het bestand is leeg of groter dan de toegestane 520 MB.")
    target = directory / ("download." + extension)
    if source != target:
        os.replace(source, target)
    return target

def main():
    if len(sys.argv) < 3 or not valid_url(sys.argv[2]):
        emit({"success": False, "message": "Ongeldige YouTube-link."})
        return 2
    action, url = sys.argv[1:3]
    try:
        if action == "info":
            info = extract_info(url)
            heights, has_audio = video_heights(info)
            emit({
                "success": True,
                "title": str(info.get("title") or "YouTube-video")[:180],
                "author": str(info.get("channel") or info.get("uploader") or "YouTube")[:120],
                "thumbnail": info.get("thumbnail"),
                "duration": int(info.get("duration") or 0),
                "qualities": heights,
                "has_audio": has_audio,
            })
            return 0

        if action != "download" or len(sys.argv) != 6:
            return 2
        mode, raw_quality, raw_directory = sys.argv[3:6]
        if mode not in {"mp4", "mp3"}:
            raise RuntimeError("Ongeldig downloadformaat.")
        quality = int(raw_quality)
        directory = Path(raw_directory).resolve()
        if not re.fullmatch(r"[a-f0-9]{40}", directory.name) or not directory.is_dir():
            raise RuntimeError("Ongeldige downloadmap.")
        write_status(directory, "processing")
        output = download(url, mode, quality, directory)
        write_status(directory, "ready", file=output.name, size=output.stat().st_size)
        return 0
    except Exception as exc:
        if action == "info":
            emit({"success": False, "message": "Deze YouTube-video is privé, beperkt, niet beschikbaar of kon niet worden geanalyseerd. Alleen openbaar toegankelijke video's worden ondersteund."})
        else:
            try:
                directory = Path(sys.argv[5]).resolve()
                if re.fullmatch(r"[a-f0-9]{40}", directory.name) and directory.is_dir():
                    write_status(directory, "failed", message=str(exc)[:300], detail=(type(exc).__name__ + ": " + repr(exc))[:700])
            except Exception:
                pass
        print(type(exc).__name__ + ": " + str(exc)[:500], file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
