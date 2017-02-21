#!/usr/bin/env sh
# Call pre-commit.php with options that work well when used as a git pre-commit hook

SCRIPT_DIR=`dirname "$(readlink -f "$0")"`
${SCRIPT_DIR}/pre-commit.php --lint --style --unit --fail-fast
