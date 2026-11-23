#!/usr/bin/env python3
"""Private CPU Demucs worker for Mashal Studio. No third-party API calls."""
import json
import os
import shutil
import subprocess
import sys
import time
from pathlib import Path

# Railpack installs all Python requirements into this exact target directory.
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
        "--mp3", "--mp3-bitrate", "192",
        "-o", str(output), str(model_input.resolve())
    ]
    result = subprocess.run(command, cwd=str(directory), capture_output=True, text=True, timeout=1800)
    if result.returncode != 0:
        raise RuntimeError((result.stderr or result.stdout or "Demucs processing failed")[-12000:])

    stem_dir = output / "htdemucs" / model_input.stem
    vocals = stem_dir / "vocals.mp3"
    instrumental = stem_dir / "no_vocals.mp3"
    if not vocals.is_file() or not instrumental.is_file():
        raise RuntimeError("Demucs heeft niet beide audiotracks aangemaakt.")

    shutil.copy2(vocals, directory / "vocals.mp3")
    shutil.copy2(instrumental, directory / "no_vocals.mp3")
    shutil.rmtree(output, ignore_errors=True)
    state("ready")
except Exception as exc:
    state("failed", error=str(exc)[-1000:])
    with (directory / "worker.log").open("a", encoding="utf-8") as log:
        log.write("\n" + repr(exc) + "\n")
finally:
    # Keep only the finished result files for the download window.
    source.unlink(missing_ok=True)
    temporary_audio.unlink(missing_ok=True)
