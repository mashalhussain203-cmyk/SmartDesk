#!/usr/bin/env python3
"""Private CPU Demucs worker for Mashal Studio. No third-party API calls."""
import json
import os
import subprocess
import sys
import time
from pathlib import Path
# Railpack installs the existing Python toolchain into this application-local target.
sys.path.insert(0, "/app/.tiktok-python")
os.environ["OMP_NUM_THREADS"] = "2"
os.environ["MKL_NUM_THREADS"] = "2"
os.environ["TORCH_HOME"] = "/app/storage/app/private/vocal-remover/model-cache"
Path(os.environ["TORCH_HOME"]).mkdir(parents=True, exist_ok=True)
from pathlib import Path

token, source, workdir = sys.argv[1:4]
directory = Path(workdir)
state_file = directory / "status.json"

def state(status, **extra):
    try:
        created_at = json.loads(state_file.read_text(encoding="utf-8")).get("created_at", int(time.time()))
    except Exception:
        created_at = int(time.time())
    data = {"status": status, "created_at": created_at, **extra}
    temp = state_file.with_suffix(".tmp")
    temp.write_text(json.dumps(data), encoding="utf-8")
    temp.replace(state_file)

try:
    import torch
    # Respect the small shared web-service CPU budget and serialize jobs.
    torch.set_num_threads(max(1, min(2, os.cpu_count() or 1)))
    state("processing")
    output = directory / "separated"
    output.mkdir(exist_ok=True)
    command = [
        sys.executable, "-m", "demucs.separate",
        "-n", "htdemucs", "--two-stems", "vocals",
        "--mp3", "--mp3-bitrate", "192",
        "-o", str(output), str(Path(source).resolve())
    ]
    result = subprocess.run(command, cwd=str(directory), capture_output=True, text=True, timeout=1800)
    if result.returncode != 0:
        (directory / "worker.log").write_text((result.stderr or result.stdout)[-12000:], encoding="utf-8")
        raise RuntimeError("Demucs processing failed")
    # Demucs names its output folder after the input basename.
    stem_dir = output / "htdemucs" / Path(source).stem
    vocals = stem_dir / "vocals.mp3"
    instrumental = stem_dir / "no_vocals.mp3"
    if not vocals.is_file() or not instrumental.is_file():
        raise RuntimeError("The separator did not produce both audio stems")
    import shutil
    shutil.copy2(vocals, directory / "vocals.mp3")
    shutil.copy2(instrumental, directory / "no_vocals.mp3")
    shutil.rmtree(output, ignore_errors=True)
    Path(source).unlink(missing_ok=True)
    state("ready")
except Exception as exc:
    state("failed", error=type(exc).__name__)
    with (directory / "worker.log").open("a", encoding="utf-8") as log:
        log.write("\n" + repr(exc) + "\n")
