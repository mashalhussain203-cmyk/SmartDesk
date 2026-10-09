# SmartDesk – private live recordings (/live)

This adds an admin-only /live page inside SmartDesk (Laravel), using the existing login and users.is_admin check. It lists, plays, downloads and deletes finalized MP4 files. Recordings are on the Laravel private local disk and are never public web files.

Accounts: knock1knock and emyii.

## Recorder setup (separate always-on service)

scripts/live-recorder/worker.mjs uses Google Chrome/Chromium + Playwright Core + FFmpeg, without a Chaturbate API token. It must run continuously on a machine that can write to the SAME Laravel storage volume. Visiting the admin page does not start the recorder.

Prerequisites, which are not installed automatically by this pull request:

- Node.js with npm install (playwright-core dependency).
- Chrome/Chromium, with CHROME_PATH pointing at the executable.
- A dedicated 1280x720 X11 display for each channel (Xvfb or a desktop).
- FFmpeg with x11grab, libx264, AAC, and PulseAudio input.
- An audio sink and matching PulseAudio monitor source per channel.
- Persistent writable Laravel storage; if using Railway, configure a persistent volume accessible to the app AND the worker. In unrelated containers without shared storage, uploaded/recorded files will NOT appear in /live.
- A working playback stream in Chrome. Normal age confirmation or login may need to be completed in the persistent Chrome profile first. This does not bypass those restrictions.

Example commands for one account, on a Linux server with these packages installed:

    npm install
    Xvfb :99 -screen 0 1280x720x24 &
    pulseaudio --start
    pactl load-module module-null-sink sink_name=LiveKnock
    LIVE_ACCOUNT=knock1knock DISPLAY=:99 PULSE_SINK=LiveKnock PULSE_SOURCE=LiveKnock.monitor CHROME_PATH=/usr/bin/google-chrome node scripts/live-recorder/worker.mjs

To monitor emyii simultaneously, start another process on its own display (:100), sink (LiveEmyii), and LIVE_ACCOUNT=emyii. Only run one worker per channel. The persistent browser profile lives under storage/app/private/live-chrome-profiles/<channel>/.

For a short recording test, set LIVE_TEST_SECONDS=30 in the worker's environment. By default, the recorder continues until offline is confirmed twice. A check runs every 60 seconds, so the first 0–60 seconds and browser startup may be missed.

## Behaviour, privacy, limitations

- The admin-only /live page refreshes recording status every 60 seconds, without refreshing the playing video.
- Status is OFFLINE only when the browser shows offline. Uncertain video playback is UNKNOWN and browser errors are ERROR.
- FFmpeg first writes an MKV. At the end, it remuxes this to MP4; only completed MP4s are shown in the private gallery. If remuxing fails, the MKV remains for recovery.
- The worker captures the full browser display, including any visible UI, at 1280x720. Audio is captured only when the Chrome audio route and PulseAudio monitor are correctly configured.
- Browser detection or recording is not guaranteed; the external site may require prompts or change its player. This feature does not bypass access controls, paywalls, or restrictions. Make sure recording is permitted under the platform terms.
- No HTTP video upload is included. Files become visible to SmartDesk because the worker saves them directly to the private storage mounted by the Laravel app.
- Every playback, download and delete endpoint requires the existing SmartDesk admin login. Files must stay outside public/ and storage/app/public.
- Long streams need substantial disk space and persistent storage. Configure backups and a supervisor/systemd to restart the worker.

## Run authorization tests

    php artisan test --filter=LiveRecordingsTest

These tests do not validate Chrome playback or an actual livestream; perform a real recording test before relying on this tool.
