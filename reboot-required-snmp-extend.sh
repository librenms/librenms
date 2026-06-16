#!/usr/bin/env bash
[ -f /var/run/reboot-required ] && echo "1" || echo "0"
