#!/usr/bin/env python3
"""TikTok public-media downloader for Mashal Studio; no SSSTik integration.

Inputs are validated TikTok links only. Output stays in a private job directory.
"""
import json
import os
import re
import shutil
import sys
import time
import urllib.parse
import urllib.request
import zipfile
from pathlib import Path

ALLOWED_TIKTOK = {"tiktok.com", "www.tiktok.com", "m.tiktok.com", "vm.tiktok.com", "vt.tiktok.com"}
IMAGE_HOST_SUFFIXES = (
    "tiktokcdn.com", "tiktokcdn-us.com", "tiktokcdn-eu.com", "tiktokv.com",
    "byteimg.com", "ibytedtos.com", "byteoversea.com", "akamaized.net", "muscdn.com"
)
MAX_SIZE = 200 * 1024 * 1024
MAX_IMAGE_SIZE = 12 * 1024 * 1024


def emit(data):
    print(json.dumps(data, ensure_ascii=False), flush=True)


def valid_url(value):
    try:
        parsed = urllib.parse.urlsplit(value)
        if parsed.scheme != "https" or parsed.hostname not in ALLOWED_TIKTOK:
            return False
        if parsed.username or parsed.password or parsed.port or len(value) > 2000:
            return False
        path = parsed.path
        if parsed.hostname in {"vm.tiktok.com", "vt.tiktok.com"}:
            return bool(re.fullmatch(r"/[A-Za-z0-9]{4,40}/?", path))
        return bool(re.search(r"/(?:video|photo)/\d{10,25}(?:/|$)", path) or
                    re.fullmatch(r"/t/[A-Za-z0-9]+/?", path))
    except ValueError:
        return False


def safe_image_url(value):
    if not isinstance(value, str) or len(value) > 4096:
        return False
    try:
        u = urllib.parse.urlsplit(value)
        host = (u.hostname or "").lower().rstrip(".")
        return (u.scheme == "https" and not u.username and not u.password and
                u.port is None and any(host == s or host.endswith("." + s) for s in IMAGE_HOST_SUFFIXES))
    except ValueError:
        return False


def photos_from_info(info):
    candidates = []
    collections = [info.get("images"), info.get("entries")]
    photo_data = info.get("image_post_info") or {}
    if isinstance(photo_data, dict):
        collections.append(photo_data.get("images"))
    for collection in collections:
        if not isinstance(collection, (list, tuple)):
            continue
        for item in collection[:30]:
            if isinstance(item, str):
                urls = [item]
            elif isinstance(item, dict):
                urls = [item.get("url"), item.get("image_url"), item.get("original_url")]
                for key in ("imageURL", "displayImage", "image"):
                    raw = item.get(key)
                    if isinstance(raw, dict):
                        urls += [raw.get("url"), raw.get("url_list", [None])[0] if isinstance(raw.get("url_list"), list) and raw.get("url_list") else None]
            else:
                continue
            for url in urls:
                if safe_image_url(url) and url not in candidates:
                    candidates.append(url)
                    break
    return candidates[:30]


def get_info(url):
    import yt_dlp
    options = {
        "quiet": True, "no_warnings": True, "noplaylist": True,
        "skip_download": True, "cachedir": False, "socket_timeout": 10,
        "retries": 1, "extractor_retries": 1,
        "ignoreerrors": False,
    }
    with yt_dlp.YoutubeDL(options) as ydl:
        info = ydl.extract_info(url, download=False)
    if not isinstance(info, dict):
        raise RuntimeError("TikTok heeft geen openbare mediagegevens teruggegeven.")
    return info


def modes_for(info):
    formats = info.get("formats") or []
    video = any(isinstance(f, dict) and f.get("vcodec") not in (None, "none")
                for f in formats)
    if not formats and info.get("url") and info.get("ext") in ("mp4", "webm"):
        video = True
    images = photos_from_info(info)
    modes = (["video", "video_hd", "audio"] if video else [])
    if images:
        modes.append("photos")
    return modes, images


def write_status(directory, status, **extras):
    data = {"status": status, "updated_at": int(time()), **extras}
    temp = directory / "status.json.tmp"
    temp.write_text(json.dumps(data, ensure_ascii=False), encoding="utf-8")
    os.replace(temp, directory / "status.json")


def download_image(url):
    class StrictRedirect(urllib.request.HTTPRedirectHandler):
        def redirect_request(self, req, fp, code, msg, headers, newurl):
            if not safe_image_url(newurl):
                raise ValueError("Onveilige redirect geweigerd")
            return super().redirect_request(req, fp, code, msg, headers, newurl)

    opener = urllib.request.build_opener(StrictRedirect)
    request = urllib.request.Request(url, headers={"User-Agent": "Mozilla/5.0", "Referer": "https://www.tiktok.com/"})
    with opener.open(request, timeout=12) as response:
        if not safe_image_url(response.geturl()):
            raise ValueError("Ongeldige afbeeldingsbron")
        mime = response.headers.get_content_type()
        if mime not in ("image/jpeg", "image/png", "image/webp"):
            raise ValueError("Geen geldig afbeeldingsbestand")
        raw = response.read(MAX_IMAGE_SIZE + 1)
        if not raw or len(raw) > MAX_IMAGE_SIZE:
            raise ValueError("Afbeelding is te groot")
        ext = {"image/jpeg": "jpg", "image/png": "png", "image/webp": "webp"}[mime]
        return raw, ext


def download_photos(urls, directory):
    if not urls:
        raise RuntimeError("Geen openbare foto's beschikbaar.")
    target = directory / "download.zip"
    saved = 0
    with zipfile.ZipFile(target, "w", compression=zipfile.ZIP_DEFLATED, compresslevel=5) as archive:
        for index, url in enumerate(urls, 1):
            try:
                data, ext = download_image(url)
                archive.writestr(f"foto-{index:02d}.{ext}", data)
                saved += 1
            except Exception:
                continue
            if target.stat().st_size > MAX_SIZE:
                raise RuntimeError("Fotobestand is te groot.")
    if saved == 0:
        raise RuntimeError("Deze TikTok-foto's zijn momenteel niet bereikbaar.")
    return target


def download_video(url, directory, mode):
    import yt_dlp
    output = str(directory / "source.%(ext)s")
    opts = {
        "quiet": True, "no_warnings": True, "noplaylist": True,
        "cachedir": False, "socket_timeout": 12,
        "retries": 2, "fragment_retries": 1, "extractor_retries": 1,
        "outtmpl": output, "max_filesize": MAX_SIZE,
        "merge_output_format": "mp4", "overwrites": True,
        "format": ("bestaudio/best" if mode == "audio" else
                   "bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best" if mode == "video_hd"
                   else "best[ext=mp4]/best"),
    }
    if mode == "audio":
        if not shutil.which("ffmpeg"):
            raise RuntimeError("MP3-conversie is momenteel niet beschikbaar.")
        opts["postprocessors"] = [{"key": "FFmpegExtractAudio", "preferredcodec": "mp3", "preferredquality": "192"}]
    elif shutil.which("ffmpeg"):
        opts["postprocessors"] = [{"key": "FFmpegVideoConvertor", "preferedformat": "mp4"}]

    with yt_dlp.YoutubeDL(opts) as ydl:
        ydl.download([url])

    ext = "mp3" if mode == "audio" else "mp4"
    files = list(directory.glob("source." + ext))
    if not files:
        raise RuntimeError("Het gewenste bestandstype is niet beschikbaar voor deze post.")
    file = files[0]
    if file.stat().st_size < 1024 or file.stat().st_size > MAX_SIZE:
        raise RuntimeError("Het mediabestand is te groot of ongeldig.")
    target = directory / ("download." + ext)
    os.replace(file, target)
    return target


def main():
    if len(sys.argv) < 3 or not valid_url(sys.argv[2]):
        emit({"success": False, "message": "Ongeldige TikTok-link."})
        return 2
    action, url = sys.argv[1:3]
    if action not in ("info", "download"):
        return 2
    try:
        info = get_info(url)
        modes, images = modes_for(info)
        duration = info.get("duration")
        if isinstance(duration, (int, float)) and duration > 600:
            raise RuntimeError("Video's langer dan 10 minuten worden niet ondersteund.")
        if action == "info":
            emit({
                "success": True,
                "title": str(info.get("title") or info.get("description") or "TikTok video")[:180],
                "creator": str(info.get("uploader") or info.get("creator") or "")[:100],
                "thumbnail": info.get("thumbnail") if safe_image_url(info.get("thumbnail")) else None,
                "duration": int(duration) if isinstance(duration, (int, float)) and duration >= 0 else None,
                "photo_count": len(images),
                "modes": modes,
            })
            return 0

        if len(sys.argv) != 5 or sys.argv[3] not in modes:
            raise RuntimeError("Gekozen formaat is niet beschikbaar.")
        mode, raw_dir = sys.argv[3:5]
        directory = Path(raw_dir).resolve()
        if not re.fullmatch(r"[a-f0-9]{40}", directory.name) or not directory.is_dir():
            raise RuntimeError("Ongeldige jobmap.")
        write_status(directory, "processing")
        result = download_photos(images, directory) if mode == "photos" else download_video(url, directory, mode)
        if result.stat().st_size > MAX_SIZE:
            raise RuntimeError("Het bestand is te groot.")
        write_status(directory, "ready", file=result.name)
        return 0
    except Exception as exc:
        if action == "info":
            emit({"success": False, "message": "De post is privé, niet bereikbaar of niet ondersteund."})
        else:
            try:
                if len(sys.argv) == 5:
                    directory = Path(sys.argv[4]).resolve()
                    if re.fullmatch(r"[a-f0-9]{40}", directory.name) and directory.is_dir():
                        write_status(directory, "failed", message="TikTok kon deze download niet leveren. Controleer of de post openbaar is.")
            except Exception:
                pass
        print(type(exc).__name__ + ": " + str(exc)[:300], file=sys.stderr)
        return 1


if __name__ == "__main__":
    sys.exit(main())
