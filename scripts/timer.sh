#!/bin/sh -e
# This wrapper script is used by systemd timers

case "$1" in
  "discovery")
    exec /opt/librenms/cronic /opt/librenms/discovery-wrapper.py 1
  ;;
  "discovery-new")
    exec /opt/librenms/discovery.php -h new
  ;;
  "poller")
    exec /opt/librenms/cronic /opt/librenms/poller-wrapper.py 16
  ;;
  "daily")
    exec /opt/librenms/daily.sh
  ;;
  "alerts")
    exec /opt/librenms/alerts.php
  ;;
  "poll-billing")
    exec /opt/librenms/poll-billing.php
  ;;
  "billing-calculate")
    exec /opt/librenms/billing-calculate.php
  ;;
  "check-services")
    exec /opt/librenms/check-services.php
  ;;
  *)
    echo "invalid command: $1"
    exit 1
esac
