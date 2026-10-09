#!/bin/sh
set -eu
chmod 700 "$XDG_RUNTIME_DIR"
Xvfb "$DISPLAY" -screen 0 1280x720x24 -nolisten tcp &
XVFB_PID=$!
sleep 2
if ! kill -0 "$XVFB_PID" 2>/dev/null; then
  echo "Xvfb failed"
  exit 1
fi
pulseaudio --start --daemonize=yes --exit-idle-time=-1
pactl load-module module-null-sink sink_name="$PULSE_SINK"
pactl set-default-sink "$PULSE_SINK"
pactl info >/dev/null
exec node /app/worker.mjs
