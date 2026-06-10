#!/usr/bin/env bash

set -e
for file in "$@"; do
    bash -n "$file"
done
set +e
