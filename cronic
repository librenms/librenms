#!/bin/bash

# Cronic v3 - cron job report wrapper
# Copyright 2007-2016 Chuck Houpt. No rights reserved, whatsoever.
# Public Domain CC0: http://creativecommons.org/publicdomain/zero/1.0/

set -eu

TMP=$(mktemp -d)
OUT=$TMP/cronic.out
ERR=$TMP/cronic.err
TRACE=$TMP/cronic.trace

set +e
"$@" >$OUT 2>$TRACE
RESULT=$?
set -e

PATTERN="^${PS4:0:1}\\+${PS4:1}"
if grep -aq "$PATTERN" $TRACE
then
    ! grep -av "$PATTERN" $TRACE > $ERR
else
    ERR=$TRACE
fi

if [ $RESULT -ne 0 -o -s "$ERR" ]
    then
    echo "Cronic detected failure or error output for the command:"
    echo "$@"
    echo
    echo "RESULT CODE: $RESULT"
    echo
    echo "ERROR OUTPUT:"
    cat "$ERR"
    echo
    echo "STANDARD OUTPUT:"
    cat "$OUT"
    if [ $TRACE != $ERR ]
    then
        echo
        echo "TRACE-ERROR OUTPUT:"
        cat "$TRACE"
    fi
fi

rm -rf "$TMP"
