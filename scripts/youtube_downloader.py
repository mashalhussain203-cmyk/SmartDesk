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

    # YouTube may reject the default web client on datacenter IPs before
    # returning formats. Try the supported public clients used by yt-dlp's
    # PO-token guidance, rather than making analysis use a different path
    # from the downloader.
    client_sets = [
        ["mweb", "tv", "web_safari"],
        ["mweb", "tv"],
        ["mweb"],
        ["web_safari"],
        None,
    ]
    last_error = None
    for clients in client_sets:
        opts = {
            "quiet": True, "no_warnings": True, "noplaylist": True,
            "skip_download": True, "cachedir": False, "socket_timeout": 25,
            "retries": 1, "extractor_retries": 1, "ignoreerrors": False,
            "js_runtimes": {"node": {}},
            "remote_components": ["ejs:github"],
        }
        if clients:
            opts["extractor_args"] = {"youtube": {"player_client": clients}}
        try:
            with yt_dlp.YoutubeDL(opts) as ydl:
                info = ydl.extract_info(url, download=False)
            if isinstance(info, dict):
                return info
            raise RuntimeError("YouTube gaf geen videogegevens terug.")
        except Exception as exc:
            last_error = exc
            message = str(exc).lower()
            retryable = (
                "sign in to confirm" in message
                or "not a bot" in message
                or "login_required" in message
                or "403" in message
                or "forbidden" in message
                or "no video formats found" in message
            )
            if not retryable:
                raise
            print(
                "[youtube-analyze] client="
                + (",".join(clients) if clients else "yt-dlp-auto")
                + " failed: " + str(exc)[:260],
                flush=True,
            )
            time.sleep(0.5)
    raise RuntimeError(str(last_error) if last_error else "YouTube-analyse mislukt.")


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


def ensure_pot_provider():
    """Install and compile bgutil's Node script provider on the persistent volume."""
    import fcntl

    home = Path("/app/storage/app/private/youtube-pot-provider")
    server = home / "server"
    script = server / "build" / "generate_once.js"
    lock_path = home.parent / ".youtube-pot-provider.lock"
    lock_path.parent.mkdir(parents=True, exist_ok=True)

    node = shutil.which("node")
    npm = shutil.which("npm")
    git = shutil.which("git")
    if not node or not npm:
        raise RuntimeError("Node.js 22+ en npm zijn vereist voor de YouTube-tokenprovider.")
    if not git:
        raise RuntimeError("Git ontbreekt op de downloadserver; de YouTube-tokenprovider kan niet worden geïnstalleerd.")

    with open(lock_path, "w", encoding="utf-8") as lock_file:
        fcntl.flock(lock_file.fileno(), fcntl.LOCK_EX)
        if not script.is_file():
            if home.exists():
                shutil.rmtree(home)
            result = subprocess.run(
                [git, "clone", "--depth", "1", "--branch", "2.0.2",
                 "https://github.com/Brainicism/bgutil-ytdlp-pot-provider.git", str(home)],
                capture_output=True, text=True, timeout=180,
            )
            if result.returncode != 0:
                raise RuntimeError("YouTube-tokenprovider downloaden mislukt: " + (result.stderr or result.stdout)[-500:])
        if not (server / "node_modules").is_dir():
            result = subprocess.run(
                [npm, "ci"],
                cwd=str(server), capture_output=True, text=True, timeout=420,
            )
            if result.returncode != 0:
                raise RuntimeError("YouTube-tokenprovider installeren mislukt: " + (result.stderr or result.stdout)[-500:])
        if not script.is_file():
            result = subprocess.run(
                [npm, "exec", "--", "tsc"],
                cwd=str(server), capture_output=True, text=True, timeout=300,
            )
            if result.returncode != 0:
                raise RuntimeError("YouTube-tokenprovider compileren mislukt: " + (result.stderr or result.stdout)[-500:])
        if not script.is_file():
            raise RuntimeError("YouTube-tokenprovider is niet correct gebouwd: generate_once.js ontbreekt.")
        fcntl.flock(lock_file.fileno(), fcntl.LOCK_UN)

    return server


class PotDebugLogger:
    """Expose only PO-token diagnostics; never log signed media URLs."""
    def debug(self, message):
        value = str(message)
        lowered = value.lower()
        if "[pot]" in lowered or "generating a " in lowered or "po token providers" in lowered:
            print("[youtube-pot] " + value, flush=True)

    def info(self, message):
        value = str(message)
        if "generating a " in value.lower() or "po token" in value.lower():
            print("[youtube-pot] " + value, flush=True)

    def warning(self, message):
        value = str(message)
        if "provider" in value.lower() or "script" in value.lower() or "token" in value.lower():
            print("[youtube-pot-warning] " + value[:500], flush=True)

    def error(self, message):
        print("[youtube-dlp-error] " + str(message)[:500], flush=True)


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
        # Audio-only formats have no height field. Never apply a height
        # filter to bestaudio; doing so can hide valid video/audio pairs.
        # Never fall back to a stream above the resolution the user selected.
        # The first selector prefers an exact-height video; the second accepts
        # the best available stream below that ceiling if a player exposes less.
        formats = [
            f"bv*[height={height}]+ba/b[height={height}]",
            f"bv*[height<={height}]+ba/b[height<={height}]",
        ]

    # Activate the bgutil PO-token generation script. The Python plugin is
    # installed in the same virtualenv as yt-dlp by requirements-tiktok.txt.
    # Build the provider on the persistent Railway volume when it is missing.
    provider_home = None
    try:
        provider_home = ensure_pot_provider()
    except Exception as exc:
        # Keep a normal yt-dlp attempt available if the optional provider cannot
        # be built (for example, during a temporary GitHub/npm outage).
        print("[youtube-pot-warning] Provider setup failed: " + str(exc)[:400], flush=True)

    # YouTube exposes different formats and token requirements per player.
    # Try modern public clients in turn; never use login cookies or private access.
    client_sets = [["mweb", "tv", "web_safari"], ["mweb", "tv"], ["mweb"], ["web_safari"], None]
    last_error = None
    network_block_error = None
    last_attempt = "onbekende speler"
    succeeded = False

    for clients in client_sets:
        for format_selector in formats:
            last_attempt = "player_client=" + (",".join(clients) if clients else "yt-dlp-auto") + "; format=" + format_selector
            for old in directory.glob("source.*"):
                old.unlink(missing_ok=True)
            common = {
                "quiet": True, "no_warnings": True, "noplaylist": True,
                "cachedir": False, "socket_timeout": 45, "retries": 3,
                "fragment_retries": 5, "extractor_retries": 3,
                "file_access_retries": 3, "outtmpl": output,
                "max_filesize": MAX_BYTES, "overwrites": True,
                "continuedl": False,
                # Explicitly enable the installed Node runtime and allow the
                # matching EJS challenge solver. Without this, YouTube signature
                # solving fails and its media URLs can return HTTP 403.
                "js_runtimes": {"node": {}},
                "remote_components": ["ejs:github"],
                "verbose": True, "logger": PotDebugLogger(),
                "format": format_selector,
            }
            extractor_args = {}
            if provider_home is not None:
                extractor_args["youtubepot-bgutilscript"] = {
                    "server_home": str(provider_home),
                }
            if clients:
                extractor_args["youtube"] = {"player_client": clients}
            if extractor_args:
                common["extractor_args"] = extractor_args
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
            if clients:
                if "web_safari" in clients and mode == "mp4":
                    # Prefer Safari HLS playlists first: current YouTube PO-token
                    # guidance notes these HLS GVS streams may be available without
                    # a GVS token, unlike many direct DASH media URLs.
                    hls_formats = [
                        f"bv*[protocol^=m3u8][height={int(quality)}]+ba/b[protocol^=m3u8][height={int(quality)}]",
                        f"bv*[protocol^=m3u8][height<={int(quality)}]+ba/b[protocol^=m3u8][height<={int(quality)}]",
                    ]
                    common["format"] = "/".join(hls_formats + [format_selector])
            try:
                with yt_dlp.YoutubeDL(common) as ydl:
                    ydl.download([url])
                # yt-dlp can return without raising even when a media request
                # was rejected or no usable output was written. Never mark that
                # attempt as successful unless a real, non-empty media file exists.
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
                if "403" in message or "forbidden" in message or "sign in to confirm" in message or "not a bot" in message:
                    network_block_error = exc
                # A 403 is commonly specific to a player client or format;
                # try the next public client/format before giving up.
                bot_challenge = ("sign in to confirm" in message or "not a bot" in message or "confirm you’re not a bot" in message)
                format_unavailable = (
                    "requested format is not available" in message
                    or "no video formats found" in message
                    or "format is not available" in message
                    or "requested format not available" in message
                )
                # YouTube's UNPLAYABLE response is client-specific in some
                # cases. Treat this like a player/format mismatch and continue
                # to the next explicitly selected public client.
                reload_required = "the page needs to be reloaded" in message
                # A player client can expose different formats than the analyser.
                # Continue with the next selector/client if this resolution is absent.
                if ("403" not in message and "forbidden" not in message
                        and not bot_challenge and not format_unavailable
                        and not reload_required):
                    raise
                time.sleep(1)
        if succeeded:
            break

    if not succeeded:
        # Prefer the original YouTube access denial over a later, less useful
        # fallback error such as an empty output file.
        effective_error = network_block_error or last_error
        raw_detail = (str(effective_error) if effective_error else "Geen foutdetails ontvangen.")
        detail = raw_detail.lower()
        if "sign in to confirm" in detail or "not a bot" in detail:
            reason = "YouTube blokkeert deze downloadserver met een botcontrole. Probeer later opnieuw of gebruik een toegestane downloadmethode."
        elif "403" in detail or "forbidden" in detail:
            reason = "YouTube weigert de mediastream vanaf de downloadserver (HTTP 403). Dit kan door ontbrekende/ongeldige PO-tokens of een netwerk-/IP-beperking komen; een lagere kwaliteit lost dit niet automatisch op."
        elif "requested format is not available" in detail or "no video formats found" in detail:
            reason = "YouTube heeft voor deze speler geen passende stream aangeboden. Kies een lagere beschikbare kwaliteit of probeer later opnieuw."
        else:
            reason = "Download mislukt (" + last_attempt + "): " + raw_detail[:220]
        if "requested format is not available" in detail or "no video formats found" in detail:
            reason = "Geen MP4-stream beschikbaar (" + last_attempt + "). Technische fout: " + raw_detail[:180]
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
