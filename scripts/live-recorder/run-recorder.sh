#!/bin/sh
set -eu
umask 077
export DISPLAY="${DISPLAY:-:99}"
Xvfb "$DISPLAY" -screen 0 1280x720x24 -nolisten tcp >/tmp/xvfb.log 2>&1 &
for i in 1 2 3 4 5 6 7 8 9 10; do
    test -S /tmp/.X11-unix/X99 && break
    sleep 1
done
test -S /tmp/.X11-unix/X99 || { cat /tmp/xvfb.log; exit 1; }

pulseaudio --start --exit-idle-time=-1
pactl load-module module-null-sink sink_name=LiveRecorder >/dev/null
pactl set-default-sink LiveRecorder
export PULSE_SOURCE=LiveRecorder.monitor
export PULSE_SINK=LiveRecorder
exec node /app/worker.mjs
