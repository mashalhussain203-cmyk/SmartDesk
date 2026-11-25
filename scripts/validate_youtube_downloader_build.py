from pathlib import Path
import subprocess
import sys

root = Path("/app")
provider_script = root / "bgutil-ytdlp-pot-provider/server/build/generate_once.js"
if not provider_script.is_file() or provider_script.stat().st_size == 0:
    raise SystemExit("bgutil PO-token script is missing")
subprocess.run(["node", "--check", str(provider_script)], check=True)
env = dict(__import__("os").environ)
env["PYTHONPATH"] = "/app/.tiktok-python"
subprocess.run(
    [sys.executable, "-c", "import yt_dlp; import yt_dlp_plugins.extractor.getpot_bgutil_script; print('bgutil PO-token plugin loaded')"],
    check=True,
    env=env,
)
subprocess.run([sys.executable, "-m", "yt_dlp", "--version"], check=True, env=env)
subprocess.run(["php", "-l", str(root / "app/Http/Controllers/YouTubeDownloaderController.php")], check=True)
subprocess.run([sys.executable, "-m", "py_compile", str(root / "scripts/youtube_downloader.py")], check=True)
print("YouTube downloader build validation passed.")
