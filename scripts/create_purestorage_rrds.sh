#!/bin/bash

RRD_DIR="/opt/librenms/rrd/172.16.7.5"
mkdir -p "$RRD_DIR"

declare -a METRICS=(
  "read_iops"
  "write_iops"
  "read_bandwidth"
  "write_bandwidth"
  "read_latency"
  "write_latency"
)

for METRIC in "${METRICS[@]}"; do
  RRD_FILE="$RRD_DIR/${METRIC}.rrd"
  if [ ! -f "$RRD_FILE" ]; then
    echo "Creating $RRD_FILE..."
    rrdtool create "$RRD_FILE" \
      DS:$METRIC:GAUGE:600:0:U \
      RRA:AVERAGE:0.5:1:600
  else
    echo "$RRD_FILE already exists."
  fi
done

echo "RRD creation complete."
