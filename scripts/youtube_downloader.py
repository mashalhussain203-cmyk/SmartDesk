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


def normalize_url(value):
    """Accept standard YouTube share URLs and strip tracking parameters."""
    try:
        p = urlsplit(value.strip())
        host = (p.hostname or "").lower()
        if p.scheme not in {"https", "http"} or host not in ALLOWED_HOSTS or p.username or p.password or p.port:
            return None
        video_id = None
        if host in {"youtu.be", "www.youtu.be"}:
            video_id = p.path.strip("/").split("/")[0]
        elif p.path == "/watch":
            from urllib.parse import parse_qs
            video_id = (parse_qs(p.query).get("v") or [None])[0]
        else:
            match = re.fullmatch(r"/(?:shorts|embed|live)/([A-Za-z0-9_-]{11})/?", p.path)
            if match:
                video_id = match.group(1)
        if not video_id or not re.fullmatch(r"[A-Za-z0-9_-]{11}", video_id):
            return None
        return "https://www.youtube.com/watch?v=" + video_id
    except (ValueError, TypeError):
        return None


def valid_url(value):
    return normalize_url(value) is not None

def write_status(directory, status, **extra):
    data = {"status": status, "updated_at": int(time.time()), **extra}
    temp = directory / "status.json.tmp"
    temp.write_text(json.dumps(data, ensure_ascii=False), encoding="utf-8")
    os.replace(temp, directory / "status.json")


def extract_info(url):
    import yt_dlp

    opts = {
        "quiet": True,
        "no_warnings": True,
        "noplaylist": True,
        "skip_download": True,
        "cachedir": False,
        "socket_timeout": 25,
        "retries": 1,
        "extractor_retries": 1,
        "ignoreerrors": False,
        "js_runtimes": {"node": {}} if shutil.which("node") else {},
        "remote_components": ["ejs:github"],
        "extractor_args": pot_extractor_args(),
    }
    try:
        with yt_dlp.YoutubeDL(opts) as ydl:
            info = ydl.extract_info(url, download=False)
        if isinstance(info, dict):
            return info
        raise RuntimeError("YouTube gaf geen videogegevens terug.")
    except Exception as exc:
        message = str(exc).lower()
        if "sign in to confirm" in message or "not a bot" in message or "login_required" in message:
            raise RuntimeError(
                "YouTube vereist aanmelding of blokkeert automatische toegang. "
                "Gebruik YouTube Studio of Google Takeout voor je eigen video's."
            ) from exc
        if "403" in message or "forbidden" in message:
            raise RuntimeError(
                "YouTube weigert toegang vanaf deze server (HTTP 403). "
                "Deze video is niet beschikbaar via een toegestane downloadroute."
            ) from exc
        raise

def pot_extractor_args():
    """Configure bgutil's script provider and the mweb client for GVS PO tokens."""
    server_home = Path(os.getenv(
        "BGUTIL_POT_SERVER_HOME",
        "/app/bgutil-ytdlp-pot-provider/server",
    )).resolve()
    script = server_home / "build" / "generate_once.js"
    if not script.is_file():
        raise RuntimeError(
            "De bgutil PO-token-generator ontbreekt op de server; "
            "de download wordt veilig afgebroken in plaats van zonder PO-token te proberen."
        )
    # yt-dlp expects extractor_args grouped by extractor/provider. The
    # provider's documented script-mode argument is server_home (not script_path).
    return {
        "youtube": {"player_client": ["mweb"]},
        "youtube-bgutilscript": {"server_home": [str(server_home)]},
    }


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




def estimate_quality_sizes(info):
    sizes = {}
    formats = [f for f in (info.get("formats") or []) if isinstance(f, dict)]
    audio_sizes = [int(f.get("filesize") or f.get("filesize_approx") or 0) for f in formats
                   if f.get("vcodec") in (None, "none") and f.get("acodec") not in (None, "none")]
    audio_size = max(audio_sizes, default=0)
    for height in {int(f.get("height") or 0) for f in formats if f.get("vcodec") not in (None, "none")}:
        candidates = [f for f in formats if int(f.get("height") or 0) == height and f.get("vcodec") not in (None, "none")]
        if not candidates:
            continue
        chosen = max(candidates, key=lambda f: int(f.get("filesize") or f.get("filesize_approx") or 0))
        size = int(chosen.get("filesize") or chosen.get("filesize_approx") or 0)
        approximate = chosen.get("filesize") is None
        if chosen.get("acodec") in (None, "none") and audio_size:
            size += audio_size
            approximate = True
        if size > 0:
            sizes[str(height)] = {"bytes": size, "approx": approximate}
    return sizes


def download(url, mode, quality, directory):
    import yt_dlp

    output = str(directory / "source.%(ext)s")
    if mode == "mp3" and not shutil.which("ffmpeg"):
        raise RuntimeError("MP3-conversie vereist FFmpeg, dat niet beschikbaar is.")

    if mode == "mp3":
        formats = ["bestaudio/best"]
    else:
        height = int(quality)
        formats = [
            f"bv*[height={height}]+ba/b[height={height}]",
            f"bv*[height<={height}]+ba/b[height<={height}]",
        ]

    last_error = None
    network_block_error = None
    last_attempt = "onbekend formaat"
    succeeded = False

    for format_selector in formats:
        last_attempt = format_selector
        for old in directory.glob("source.*"):
            old.unlink(missing_ok=True)

        common = {
            "quiet": True,
            "no_warnings": True,
            "noplaylist": True,
            "cachedir": False,
            "socket_timeout": 45,
            "retries": 3,
            "fragment_retries": 5,
            "extractor_retries": 3,
            "file_access_retries": 3,
            "outtmpl": output,
            "max_filesize": MAX_BYTES,
            "overwrites": True,
            "continuedl": False,
            "js_runtimes": {"node": {}} if shutil.which("node") else {},
            "remote_components": ["ejs:github"],
            "extractor_args": pot_extractor_args(),
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
            produced = [
                p for p in directory.glob("source.*")
                if p.is_file()
                and p.suffix.lower() in {".mp4", ".mkv", ".webm", ".mov", ".mp3", ".m4a", ".opus", ".ogg"}
                and p.stat().st_size >= 1024
            ]
            if not produced:
                raise RuntimeError("yt-dlp beëindigde de poging zonder een bruikbaar mediabestand te maken.")
            succeeded = True
            break
        except Exception as exc:
            last_error = exc
            message = (repr(exc) + " " + str(exc)).lower()
            if "403" in message or "forbidden" in message:
                network_block_error = exc
            bot_challenge = (
                "sign in to confirm" in message
                or "not a bot" in message
                or "confirm you’re not a bot" in message
                or "login_required" in message
            )
            format_unavailable = (
                "requested format is not available" in message
                or "no video formats found" in message
                or "format is not available" in message
                or "requested format not available" in message
            )
            if not bot_challenge and not format_unavailable and "403" not in message and "forbidden" not in message:
                raise
            time.sleep(1)

    if not succeeded:
        effective_error = network_block_error or last_error
        raw_detail = str(effective_error) if effective_error else "Geen foutdetails ontvangen."
        detail = raw_detail.lower()
        if "sign in to confirm" in detail or "not a bot" in detail or "login_required" in detail:
            reason = (
                "YouTube vereist aanmelding of blokkeert automatische toegang. "
                "Gebruik YouTube Studio of Google Takeout voor je eigen video's."
            )
        elif "403" in detail or "forbidden" in detail:
            reason = (
                "YouTube weigert toegang vanaf deze server (HTTP 403). "
                "De video is niet beschikbaar via een toegestane downloadroute."
            )
        elif "requested format is not available" in detail or "no video formats found" in detail:
            reason = "Geen passende MP4-stream beschikbaar (" + last_attempt + ")."
        else:
            reason = "Download mislukt (" + last_attempt + "): " + raw_detail[:220]
        raise RuntimeError(reason) from last_error


    extension = "mp3" if mode == "mp3" else "mp4"
    if mode == "mp4":
        # Always create a standards-compatible MP4 instead of assuming that
        # a remuxed source file is already H.264/AAC and browser compatible.
        ffmpeg = shutil.which("ffmpeg")
        if not ffmpeg:
            raise RuntimeError("FFmpeg ontbreekt op de downloadserver; MP4-conversie kan niet worden uitgevoerd.")
        sources = [
            p for p in directory.glob("source.*")
            if p.is_file()
            and p.suffix.lower() in {".mp4", ".mkv", ".webm", ".mov", ".m4v"}
            and p.stat().st_size >= 1024
        ]
        if not sources:
            raise RuntimeError("YouTube heeft geen videobestand geleverd; er is niets om naar MP4 om te zetten.")
        source = max(sources, key=lambda p: p.stat().st_size)
        target = directory / "download.mp4"
        result = subprocess.run([
            ffmpeg, "-y", "-i", str(source),
            "-map", "0:v:0", "-map", "0:a:0?",
            "-c:v", "libx264", "-preset", "veryfast", "-crf", "21",
            "-pix_fmt", "yuv420p", "-c:a", "aac", "-b:a", "192k",
            "-movflags", "+faststart", "-f", "mp4", str(target)
        ], capture_output=True, text=True, timeout=900)
        if result.returncode != 0 or not target.is_file() or target.stat().st_size < 1024:
            target.unlink(missing_ok=True)
            detail = (result.stderr or result.stdout or "onbekende FFmpeg-fout")[-500:]
            raise RuntimeError("FFmpeg kon de gedownloade video niet naar MP4 omzetten: " + detail)
        for item in sources:
            if item != target:
                item.unlink(missing_ok=True)
        source = target
    else:
        candidates = list(directory.glob("source.mp3"))
        if not candidates:
            raise RuntimeError("YouTube heeft het gevraagde MP3-bestand niet geleverd.")
        source = candidates[0]
        target = directory / "download.mp3"
        if source != target:
            os.replace(source, target)
        source = target
    if mode == "mp4":
        probe = shutil.which("ffprobe")
        if not probe:
            raise RuntimeError("FFprobe ontbreekt; de MP4 kan niet veilig worden gecontroleerd.")
        checked = subprocess.run(
            [probe, "-v", "error", "-select_streams", "v:0",
             "-show_entries", "stream=height", "-of", "csv=p=0", str(source)],
            capture_output=True, text=True, timeout=30,
        )
        if checked.returncode != 0:
            raise RuntimeError("Het gemaakte videobestand kon niet worden gecontroleerd.")
        try:
            actual_height = int(checked.stdout.strip().splitlines()[0])
        except (ValueError, IndexError):
            raise RuntimeError("Het gemaakte MP4-bestand bevat geen geldige videostream.")
        if actual_height < 1 or actual_height > int(quality):
            raise RuntimeError("De server heeft een andere resolutie geleverd dan gevraagd; het bestand wordt niet aangeboden.")
    target = directory / ("download." + extension)
    if source != target:
        os.replace(source, target)
    return target

def main():
    if len(sys.argv) < 3 or not valid_url(sys.argv[2]):
        emit({"success": False, "message": "Ongeldige YouTube-link."})
        return 2
    action, raw_url = sys.argv[1:3]
    url = normalize_url(raw_url)
    if not url:
        emit({"success": False, "message": "Ongeldige YouTube-link."})
        return 2
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
                "view_count": info.get("view_count"),
                "like_count": info.get("like_count"),
                "comment_count": info.get("comment_count"),
                "quality_sizes": estimate_quality_sizes(info),
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
    except SystemExit as exc:
        # Some extractor/plugin paths may terminate the interpreter directly.
        # Do not let SystemExit(0) look like a successful, empty analysis.
        detail = "yt-dlp beëindigde het analyseproces onverwacht (SystemExit: " + repr(exc.code) + ")."
        if action == "info":
            emit({"success": False, "message": detail})
        else:
            try:
                directory = Path(sys.argv[5]).resolve()
                if re.fullmatch(r"[a-f0-9]{40}", directory.name) and directory.is_dir():
                    write_status(directory, "failed", message=detail, detail=detail)
            except Exception:
                pass
        print(detail, file=sys.stderr)
        return 1
    except Exception as exc:
        if action == "info":
            safe_message = str(exc)
            if not (
                safe_message.startswith("YouTube vereist aanmelding")
                or safe_message.startswith("YouTube weigert toegang")
            ):
                safe_message = (
                    "Deze YouTube-video is privé, beperkt, niet beschikbaar of kon niet worden "
                    "geanalyseerd. Alleen openbaar toegankelijke video's worden ondersteund."
                )
            emit({"success": False, "message": safe_message[:300]})
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
