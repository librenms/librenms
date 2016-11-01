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
    if [ "$up" -eq 0 ]; then
        $0 no-code-update
        exit
    elif [ "$up" -eq 1 ]; then
        # Update to Master-Branch
        old_ver=$(git show --pretty="%H" -s HEAD)
        status_run 'Updating to latest codebase' 'git pull --quiet'
        new_ver=$(git show --pretty="%H" -s HEAD)
        if [ "$old_ver" != "$new_ver" ]; then
            status_run "Updated from $old_ver to $new_ver" ''
        fi
    elif [ "$up" -eq 3 ]; then
        # Update to last Tag
        old_ver=$(git describe --exact-match --tags $(git log -n1 --pretty='%h'))
        status_run 'Updating to latest release' 'git fetch --tags && git checkout $(git describe --tags $(git rev-list --tags --max-count=1))'
        new_ver=$(git describe --exact-match --tags $(git log -n1 --pretty='%h'))
        if [ "$old_ver" != "$new_ver" ]; then
            status_run "Updated from $old_ver to $new_ver" ''
        fi
    fi

    cnf=$(echo $(grep '\[.distributed_poller.\]' config.php | egrep -v -e '^//' -e '^#' | cut -d = -f 2 | sed 's/;//g'))
    if ((${BASH_VERSINFO[0]} < 4)); then
        cnf=`echo $cnf|tr [:upper:] [:lower:]`
    else
        cnf=${cnf,,}
    fi

    if [ -z "$cnf" ] || [ "$cnf" == "0" ] || [ "$cnf" == "false" ]; then
        # Call ourself again in case above pull changed or added something to daily.sh
        $0 post-pull
    fi
else
    case $arg in
        no-code-update)
            # Updates of the code are disabled, just check for schema updates
            # and clean up the db.
            status_run 'Updating SQL-Schema' 'php includes/sql-schema/update.php'
            status_run 'Cleaning up DB' "$0 cleanup"
        ;;
        post-pull)
            # List all tasks to do after pull in the order of execution
            status_run 'Updating SQL-Schema' 'php includes/sql-schema/update.php'
            status_run 'Updating submodules' "$0 submodules"
            status_run 'Cleaning up DB' "$0 cleanup"
            status_run 'Fetching notifications' "$0 notifications"
        ;;
        cleanup)
            # Cleanups
            php daily.php -f refresh_alert_rules
            php daily.php -f syslog
            php daily.php -f eventlog
            php daily.php -f authlog
            php daily.php -f perf_times
            php daily.php -f callback
            php daily.php -f device_perf
            php daily.php -f purgeusers
            php daily.php -f bill_data
            php daily.php -f alert_log
            php daily.php -f rrd_purge
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
