#!/bin/sh

# Script to convert RRDs from the experimental first release of MIB-based polling.

set -e
set -u

for i; do
    base=${i%%.rrd}
    if [ -e $i.old ]; then
	continue
    fi
    mv $i $i.old
    echo "Processing $i"
    rrdtool dump $i.old | sed -e 's|<name>.*</name>|<name>mibval</name>|' > $base.xml
    rrdtool restore $base.xml $i
    rm -f $base.xml
done

exit 0
