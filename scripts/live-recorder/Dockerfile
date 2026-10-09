FROM node:22-bookworm-slim

ENV DEBIAN_FRONTEND=noninteractive
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       chromium ffmpeg xvfb pulseaudio pulseaudio-utils util-linux ca-certificates fonts-liberation \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY scripts/live-recorder/package.json /app/package.json
RUN npm install --omit=dev --no-audit --no-fund
COPY scripts/live-recorder/worker.mjs /app/worker.mjs
COPY scripts/live-recorder/schedule.mjs /app/schedule.mjs
COPY scripts/live-recorder/age-consent.mjs /app/age-consent.mjs
COPY scripts/live-recorder/access-diagnostics.mjs /app/access-diagnostics.mjs
COPY scripts/live-recorder/docker-entrypoint.sh /usr/local/bin/live-recorder-start
COPY scripts/live-recorder/docker-root-entrypoint.sh /usr/local/bin/live-recorder-root-start
RUN chmod +x /usr/local/bin/live-recorder-start /usr/local/bin/live-recorder-root-start \
    && mkdir -p /tmp/live-profile /tmp/live-runtime \
    && chown -R node:node /tmp/live-profile /tmp/live-runtime /app

USER root
ENV DISPLAY=:99
ENV CHROME_PATH=/usr/bin/chromium
ENV XDG_RUNTIME_DIR=/tmp/live-runtime
ENV PULSE_SINK=LiveRecorder
ENV PULSE_SOURCE=LiveRecorder.monitor
ENV LIVE_PROFILE_ROOT=/tmp/live-profile
CMD ["/usr/local/bin/live-recorder-root-start"]
