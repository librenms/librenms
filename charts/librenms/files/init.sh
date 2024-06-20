#!/bin/sh
TARGET="/data/env-volume/env"
echo "Target: $TARGET"
cp /data/files/env $TARGET
echo -e "\nNODE_ID=`hostname`" >> $TARGET

cat $TARGET
