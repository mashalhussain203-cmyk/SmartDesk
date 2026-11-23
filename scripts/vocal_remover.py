#!/usr/bin/env python3
"""Private CPU Demucs worker for Mashal Studio. No third-party API calls."""
import json
import os
import re
import selectors
import shutil
import subprocess
import sys
import time
from pathlib import Path

# Railpack installs requirements into this target directory during build.
PACKAGE_DIR = "/app/.tiktok-python"
sys.path.insert(0, PACKAGE_DIR)
os.environ["PYTHONPATH"] = PACKAGE_DIR + os.pathsep + os.environ.get("PYTHONPATH", "")
os.environ["OMP_NUM_THREADS"] = "2"
os.environ["MKL_NUM_THREADS"] = "2"
os.environ["TORCH_HOME"] = "/app/storage/app/private/vocal-remover/model-cache"
Path(os.environ["TORCH_HOME"]).mkdir(parents=True, exist_ok=True)

token, source_arg, workdir = sys.argv[1:4]
directory = Path(workdir)
source = Path(source_arg)
state_file = directory / "status.json"
temporary_audio = directory / "extracted-audio.wav"

def state(status, **extra):
    try:
        created_at = json.loads(state_file.read_text(encoding="utf-8")).get("created_at", int(time.time()))
    except Exception:
        created_at = int(time.time())
    data = {"status": status, "created_at": created_at, **extra}
    temp = state_file.with_suffix(".tmp")
    temp.write_text(json.dumps(data), encoding="utf-8")
    temp.replace(state_file)

def run_checked(command, timeout):
    result = subprocess.run(command, cwd=str(directory), capture_output=True, text=True, timeout=timeout)
    if result.returncode != 0:
        detail = (result.stderr or result.stdout or "Unknown media processing error")[-10000:]
        raise RuntimeError(detail)
    return result.stdout.strip()

try:
    import torch
    torch.set_num_threads(max(1, min(2, os.cpu_count() or 1)))
    state("processing", stage="checking_media")

    duration_text = run_checked([
        "ffprobe", "-v", "error", "-show_entries", "format=duration",
        "-of", "default=noprint_wrappers=1:nokey=1", str(source)
    ], 90)
    duration = float(duration_text)
    if duration <= 0 or duration > 600:
        raise RuntimeError("Bestand moet een leesbaar audio-/videobestand van maximaal 10 minuten zijn.")

    audio_stream = run_checked([
        "ffprobe", "-v", "error", "-select_streams", "a:0",
        "-show_entries", "stream=codec_type", "-of", "csv=p=0", str(source)
    ], 90)
    if audio_stream.strip() != "audio":
        raise RuntimeError("Dit bestand bevat geen leesbaar audiospoor.")

    model_input = source
    if source.suffix.lower() in {".mp4", ".mov", ".webm"}:
        state("processing", stage="extracting_audio")
        run_checked([
            "ffmpeg", "-nostdin", "-y", "-i", str(source),
            "-vn", "-ac", "2", "-ar", "44100", "-c:a", "pcm_s16le",
            str(temporary_audio)
        ], 300)
        if not temporary_audio.is_file() or temporary_audio.stat().st_size < 1000:
            raise RuntimeError("Audio uit het videobestand kon niet worden uitgelezen.")
        model_input = temporary_audio

    state("processing", stage="separating")
    output = directory / "separated"
    output.mkdir(exist_ok=True)
    command = [
        sys.executable, "-m", "demucs.separate",
        "-n", "htdemucs", "--two-stems", "vocals",
        "--shifts", "0", "--segment", "3", "--overlap", "0.25",
        "--mp3", "--mp3-bitrate", "192",
        "-o", str(output), str(model_input.resolve())
    ]
    # Report model loading separately, then stream tqdm progress to status.json.
    state("processing", stage="loading_model", progress=0)
    process = subprocess.Popen(command, cwd=str(directory), stdout=subprocess.PIPE,
                               stderr=subprocess.STDOUT, bufsize=0)
    output_tail = ""
    pending = ""
    progress_value = 0
    last_state_write = 0.0
    started = time.monotonic()
    with selectors.DefaultSelector() as selector:
        selector.register(process.stdout, selectors.EVENT_READ)
        while process.poll() is None:
            if time.monotonic() - started > 1800:
                process.kill()
                raise RuntimeError("Audioverwerking duurde langer dan 30 minuten en is gestopt.")
            for key, _ in selector.select(timeout=1):
                chunk = os.read(key.fd, 4096)
                if not chunk:
                    selector.unregister(key.fileobj)
                    continue
                decoded = chunk.decode("utf-8", errors="replace")
                output_tail = (output_tail + decoded)[-12000:]
                pending += decoded
                parts = re.split(r"[\r\n]+", pending)
                pending = parts.pop() if parts else ""
                for line in parts:
                    match = re.search(r"(\d{1,3})%|([\d.]+)\s*/\s*([\d.]+)\s*\[", line)
                    if not match:
                        continue
                    if match.group(1) is not None:
                        progress_value = max(progress_value, min(99, int(match.group(1))))
                    elif float(match.group(3)) > 0:
                        progress_value = max(progress_value, min(99, int(float(match.group(2)) / float(match.group(3)) * 100)))
                    now = time.monotonic()
                    if progress_value > 0 or now - last_state_write >= 5:
                        state("processing", stage="separating", progress=progress_value)
                        last_state_write = now
        remainder = process.stdout.read().decode("utf-8", errors="replace") if process.stdout else ""
        output_tail = (output_tail + remainder)[-12000:]
    if process.returncode != 0:
        raise RuntimeError((output_tail or "Demucs processing failed")[-12000:])

    stem_dir = output / "htdemucs" / model_input.stem
    vocals = stem_dir / "vocals.mp3"
    instrumental = stem_dir / "no_vocals.mp3"
    if not vocals.is_file() or not instrumental.is_file():
        raise RuntimeError("Demucs heeft niet beide audiotracks aangemaakt.")

    shutil.copy2(vocals, directory / "vocals.mp3")
    shutil.copy2(instrumental, directory / "no_vocals.mp3")
    shutil.rmtree(output, ignore_errors=True)
    state("ready", progress=100)
except Exception as exc:
    state("failed", error=str(exc)[-1000:])
    with (directory / "worker.log").open("a", encoding="utf-8") as log:
        log.write("\n" + repr(exc) + "\n")
finally:
    # Remove the original upload and all intermediate data on success or failure.
    source.unlink(missing_ok=True)
    temporary_audio.unlink(missing_ok=True)
    shutil.rmtree(directory / "separated", ignore_errors=True)

    # Keep only the two downloadable MP3s for at most one hour, then erase the job.
    try:
        created_at = int(json.loads(state_file.read_text(encoding="utf-8")).get("created_at", time.time()))
    except Exception:
        created_at = int(time.time())
    time.sleep(max(0, 3600 - (time.time() - created_at)))
    shutil.rmtree(directory, ignore_errors=True)
