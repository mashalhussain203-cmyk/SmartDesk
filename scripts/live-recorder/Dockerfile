FROM node:22-bookworm-slim
ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update && apt-get install -y --no-install-recommends \
    chromium ffmpeg xvfb pulseaudio pulseaudio-utils \
    ca-certificates fonts-liberation util-linux \
 && rm -rf /var/lib/apt/lists/* \
 && useradd --uid 10001 --create-home --shell /bin/sh recorder
WORKDIR /app
COPY scripts/live-recorder/package.json ./package.json
RUN npm install --omit=dev --no-audit --no-fund
COPY scripts/live-recorder/worker.mjs scripts/live-recorder/private-upload.mjs ./
COPY scripts/live-recorder/entrypoint.sh scripts/live-recorder/run-recorder.sh ./
RUN chmod 755 ./entrypoint.sh ./run-recorder.sh
ENV CHROME_PATH=/usr/bin/chromium \
    DISPLAY=:99 \
    PULSE_SOURCE=LiveRecorder.monitor \
    LIVE_STORAGE_ROOT=/data/live-recordings \
    LIVE_PROFILE_ROOT=/data/profiles
CMD ["/app/entrypoint.sh"]
