#!/bin/sh
set -eu
umask 077
mkdir -p /tmp/live-profile /tmp/live-runtime
chown -R node:node /tmp/live-profile /tmp/live-runtime
chmod 700 /tmp/live-runtime
exec runuser -u node -- /usr/local/bin/live-recorder-start
