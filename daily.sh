#!/usr/bin/env bash
# Copyright (C) 2015 Daniel Preussker, QuxLabs UG <preussker@quxlabs.com>
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

cd "$(dirname "$0")"
arg="$1"

# Fancy-Print and run commands
# @arg    Text
# @arg    Command
# @return Exit-Code of Command
status_run() {
    printf "%-50s" "$1"
    echo "$1" >> logs/daily.log
    tmp=$(bash -c "$2" 2>&1)
    ex=$?
    echo "$tmp" >> logs/daily.log
    echo "Returned: $ex" >> logs/daily.log
    [ $ex -eq 0 ] && echo -e ' \033[0;32mOK\033[0m' || echo -e ' \033[0;31mFAIL\033[0m'
    return $ex
}

if [ -z "$arg" ]; then
    up=$(php daily.php -f update >&2; echo $?)
    if [ "$up" -eq 1 ]; then
        # Update to Master-Branch
        status_run 'Updating to latest codebase' 'git pull --quiet'
    elif [ "$up" -eq 3 ]; then
        # Update to last Tag
        status_run 'Updating to latest release' 'git fetch --tags && git checkout $(git describe --tags $(git rev-list --tags --max-count=1))'
    fi

    cnf=$(echo $(grep '\[.distributed_poller.\]' config.php | egrep -v -e '^//' -e '^#' | cut -d = -f 2 | sed 's/;//g'))
    cnd=${cnf,,}
    if [ -z "$cnf" ] || [ "$cnf" == "0" ] || [ "$cnf" == "false" ]; then
        # Call ourself again in case above pull changed or added something to daily.sh
        $0 post-pull
    fi
else
    case $arg in
        post-pull)
            # List all tasks to do after pull in the order of execution
            status_run 'Updating SQL-Schema' 'php includes/sql-schema/update.php'
            status_run 'Updating submodules' "$0 submodules"
            status_run 'Cleaning up DB' "$0 cleanup"
            status_run 'Fetching notifications' "$0 notifications"
        ;;
        cleanup)
            # DB-Cleanups
            php daily.php -f syslog
            php daily.php -f eventlog
            php daily.php -f authlog
            php daily.php -f perf_times
            php daily.php -f callback
            php daily.php -f device_perf
            php daily.php -f purgeusers
        ;;
        submodules)
            # Init+Update our submodules
            git submodule --quiet init
            git submodule --quiet update
        ;;
        notifications)
            # Get notifications
            php daily.php -f notifications
        ;;
    esac
fi
