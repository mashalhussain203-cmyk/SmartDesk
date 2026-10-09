#!/bin/sh
set -eu
umask 077
mkdir -p /data/live-recordings /data/profiles
chown recorder:recorder /data /data/live-recordings /data/profiles
exec runuser -u recorder -- /app/run-recorder.sh
