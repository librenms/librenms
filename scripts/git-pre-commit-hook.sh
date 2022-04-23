#!/usr/bin/env sh
# Call ./lnms dev:check with options that work well when used as a git pre-commit hook

SCRIPT_DIR=`dirname "$(readlink -f "$0")"`
${SCRIPT_DIR}/lnms dev:check
