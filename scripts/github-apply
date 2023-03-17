#!/bin/sh

LIBRENMS_DIR=$(dirname "$(readlink -f "$0/..")")
cd "$LIBRENMS_DIR" || exit
for pr in "$@"
    do
        case $pr in
        ''|*[!0-9]*) echo "You must specify a PR number to apply a patch" ;;
        *) curl -s https://patch-diff.githubusercontent.com/raw/librenms/librenms/pull/"${pr}".diff | git apply --exclude=*.png -v ;;
    esac
done

