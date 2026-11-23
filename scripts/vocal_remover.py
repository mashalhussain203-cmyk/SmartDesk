#!/usr/bin/env python3
"""Private CPU Demucs worker for Mashal Studio. No third-party inference API."""
import importlib.util
import json
import os
import re
import selectors
import shutil
import subprocess
import sys
import time
from pathlib import Path

# Railpack installs the audio dependencies into this shared target directory.
# Add it before importing or locating Demucs so the runtime uses the built packages.
PACKAGE_DIR = "/app/.tiktok-python"
if os.path.isdir(PACKAGE_DIR) and PACKAGE_DIR not in sys.path:
    sys.path.insert(0, PACKAGE_DIR)

# Keep CPU and memory use predictable on the small Railway instance.
os.environ["OMP_NUM_THREADS"] = "2"
os.environ["MKL_NUM_THREADS"] = "2"
os.environ["OPENBLAS_NUM_THREADS"] = "1"
os.environ["NUMEXPR_NUM_THREADS"] = "1"
os.environ["PYTHONUNBUFFERED"] = "1"
os.environ["TORCH_HOME"] = "/app/storage/app/private/vocal-remover/model-cache"
Path(os.environ["TORCH_HOME"]).mkdir(parents=True, exist_ok=True)

token, source_arg, workdir = sys.argv[1:4]
directory = Path(workdir)
source = Path(source_arg)
state_file = directory / "status.json"
temporary_audio = directory / "extracted-audio.wav"
worker_log = directory / "worker.log"
job_started = time.time()


def state(status, **extra):
    try:
        previous = json.loads(state_file.read_text(encoding="utf-8"))
        created_at = previous.get("created_at", int(job_started))
    except Exception:
        created_at = int(job_started)
    data = {
        "status": status,
        "created_at": created_at,
        "updated_at": int(time.time()),
        "elapsed_seconds": max(0, int(time.time() - job_started)),
        **extra,
    }
    temp = state_file.with_suffix(".tmp")
    temp.write_text(json.dumps(data), encoding="utf-8")
    temp.replace(state_file)


def log(message):
    with worker_log.open("a", encoding="utf-8") as stream:
        stream.write(message.rstrip() + "\n")
        stream.flush()


def run_checked(command, timeout):
    result = subprocess.run(
        command, cwd=str(directory), capture_output=True, text=True, timeout=timeout
    )
    if result.returncode != 0:
        detail = (result.stderr or result.stdout or "Unknown media processing error")[-10000:]
        raise RuntimeError(detail)
    return result.stdout.strip()


def update_progress(text, current):
    match = re.search(r"(\d{1,3})%", text)
    if match:
        return max(current, min(99, int(match.group(1))))
    match = re.search(r"([\d.]+)\s*/\s*([\d.]+)\s*\[", text)
    if match and float(match.group(2)) > 0:
        return max(current, min(99, int(float(match.group(1)) / float(match.group(2)) * 100)))
    return current


try:
    demucs_spec = importlib.util.find_spec("demucs")
    if demucs_spec is None:
        raise RuntimeError(f"Demucs is niet geïnstalleerd in deze Python-omgeving: {sys.executable}")
    log(f"Worker interpreter: {sys.executable}")
    log(f"Demucs module: {demucs_spec.origin}")
    if not source.is_file() or source.stat().st_size < 1:
        raise RuntimeError("Het geüploade audiobestand ontbreekt of is leeg.")

    state("processing", stage="checking_media", progress=0)
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
        state("processing", stage="extracting_audio", progress=0)
        run_checked([
            "ffmpeg", "-nostdin", "-y", "-i", str(source),
            "-vn", "-ac", "2", "-ar", "44100", "-c:a", "pcm_s16le",
            str(temporary_audio)
        ], 300)
        if not temporary_audio.is_file() or temporary_audio.stat().st_size < 1000:
            raise RuntimeError("Audio uit het videobestand kon niet worden uitgelezen.")
        model_input = temporary_audio

    output = directory / "separated"
    output.mkdir(exist_ok=True)
    command = [
        sys.executable, "-m", "demucs.separate",
        "-n", "htdemucs", "--two-stems", "vocals",
        "--shifts", "0", "--segment", "1.5", "--overlap", "0.1", "--device", "cpu",
        "--mp3", "--mp3-bitrate", "192",
        "-o", str(output), str(model_input.resolve())
    ]

    # IMPORTANT: Do not import torch in this supervisor process.
    # Demucs runs in its own Python process; importing torch here too nearly
    # doubles the RAM footprint on Railway's 1 GB instance.
    state("processing", stage="loading_model", progress=0)
    log("Starting Demucs CPU worker; model=htdemucs; threads=2")
    process = subprocess.Popen(
        command,
        cwd=str(directory),
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        bufsize=0,
        start_new_session=True,
    )
    output_tail = ""
    pending = ""
    progress_value = 0
    last_state_write = 0.0
    current_stage = "separating"
    deadline = time.monotonic() + 1800

    with selectors.DefaultSelector() as selector:
        selector.register(process.stdout, selectors.EVENT_READ)
        while selector.get_map():
            if process.poll() is None and time.monotonic() > deadline:
                process.kill()
                process.wait()
                raise RuntimeError("Audioverwerking duurde langer dan 30 minuten en is gestopt.")

            events = selector.select(timeout=1)
            if not events:
                now = time.monotonic()
                if process.poll() is None and now - last_state_write >= 5:
                    state("processing", stage=current_stage, progress=progress_value)
                    last_state_write = now
                continue

            for key, _ in events:
                chunk = os.read(key.fd, 4096)
                if not chunk:
                    selector.unregister(key.fileobj)
                    continue

                decoded = chunk.decode("utf-8", errors="replace")
                output_tail = (output_tail + decoded)[-12000:]
                log(decoded)
                pending += decoded
                parts = re.split(r"[\r\n]+", pending)
                pending = parts.pop() if parts else ""

                for line in parts:
                    if "Separating track" in line or "%" in line:
                        current_stage = "separating"
                    new_progress = update_progress(line, progress_value)
                    if new_progress != progress_value:
                        progress_value = new_progress
                    now = time.monotonic()
                    if progress_value > 0 or now - last_state_write >= 5:
                        state(
                            "processing",
                            stage="separating",
                            progress=progress_value,
                        )
                        last_state_write = now

    return_code = process.wait()
    if return_code != 0:
        raise RuntimeError((output_tail or "Demucs processing failed")[-12000:])

    stem_dir = output / "htdemucs" / model_input.stem
    vocals = stem_dir / "vocals.mp3"
    instrumental = stem_dir / "no_vocals.mp3"
    if not vocals.is_file() or not instrumental.is_file():
        raise RuntimeError("Demucs heeft niet beide audiotracks aangemaakt.")
    if vocals.stat().st_size < 1000 or instrumental.stat().st_size < 1000:
        raise RuntimeError("Demucs heeft een onvolledig audiobestand aangemaakt.")

    shutil.copy2(vocals, directory / "vocals.mp3")
    shutil.copy2(instrumental, directory / "no_vocals.mp3")
    shutil.rmtree(output, ignore_errors=True)
    state("ready", stage="complete", progress=100)
    log("Vocal Remover finished successfully; both MP3 stems are ready.")

except Exception as exc:
    state("failed", stage="failed", error=str(exc)[-1000:])
    log("ERROR: " + repr(exc))

finally:
    # Remove the original upload and intermediate data on success or failure.
    source.unlink(missing_ok=True)
    temporary_audio.unlink(missing_ok=True)
    shutil.rmtree(directory / "separated", ignore_errors=True)

    # Keep only the two downloadable MP3s for at most one hour.
    try:
        created_at = int(json.loads(state_file.read_text(encoding="utf-8")).get("created_at", time.time()))
    except Exception:
        created_at = int(time.time())
    time.sleep(max(0, 3600 - (time.time() - created_at)))
    shutil.rmtree(directory, ignore_errors=True)
