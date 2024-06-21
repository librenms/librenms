#!/bin/sh
TARGET="/data/env-volume/env"
printf "Target: $TARGET"
cp /data/files/env $TARGET
printf "\nNODE_ID=$(hostname)" >> $TARGET

cat $TARGET
