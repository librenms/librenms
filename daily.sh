#!/usr/bin/env bash
################################################################################
# Copyright (C) 2015 Daniel Preussker, QuxLabs UG <preussker@quxlabs.com>
# Layne "Gorian" Breitkreutz <Layne.Breitkreutz@thelenon.com>
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
################################################################################

#######################################
# CONSTANTS
#######################################
# define DIR_LIBRENMS as the directory this script is called from
DIR_LIBRENMS=$(dirname "$(readlink -f "$0")")

# set log_file, using librenms $config['log_dir'], if set
# otherwise we default to <LibreNMS Install Directory>/logs
LOG_DIR=$(php -r "include '${DIR_LIBRENMS}/config.php'; echo isset(\$config['log_dir']) ? \$config['log_dir'] : '${DIR_LIBRENMS}/logs';")


#######################################
# Fancy-Print and run commands
# Globals:
#   LOG_DIR
# Arguments:
#   Text
#   Command
# Returns:
#   Exit-Code of Command
#######################################
status_run() {
    # Explicitly define our arguments
    local args="$@";
    local arg_text=$1;
    local arg_command=$2;
    local log_file;
    local exit_code;
    local tmp;
    local log_file=${LOG_DIR}/daily.log;

    # set log_file, using librenms $config['log_dir'], if set
    # otherwise we default to ./logs/daily.log

    printf "%-50s" "${arg_text}";
    echo "${arg_text}" >> ${log_file}
    tmp=$(bash -c "${arg_command}" 2>&1);
    exit_code=$?
    echo "${tmp}" >> ${log_file}
    echo "Returned: ${exit_code}" >> ${log_file}

    # print OK if the command ran succesfully
    # or FAIL otherwise (non-zero exit code)
    if [[ "${exit_code}" == "0" ]]; then
        printf " \033[0;32mOK\033[0m\n";
    else
        printf " \033[0;31mFAIL\033[0m\n";
    fi
    return ${exit_code}
}

#######################################
# Call daily.php
# Globals:
#   DIR_LIBRENMS
# Arguments:
#   args:
#        Array of arguments to pass to
#        daily.php 
# Returns:
#   Exit-Code of Command
#######################################
call_daily_php() {
    local args=( "$@" );

    for arg in "${args[@]}"; do
        php "${DIR_LIBRENMS}/daily.php" -f "${arg}";
    done
}

#######################################
# Entry into program
# Globals:
#   DIR_LIBRENMS
# Arguments:
#   
# Returns:
#   Exit-Code of Command
#######################################
main () {
    local arg="$1";
    cd ${DIR_LIBRENMS};

    if [[ -z "$arg" ]]; then
        up=$(php daily.php -f update >&2; echo $?)
        if [[ "$up" == "0" ]]; then
            $0 no-code-update
            exit
        elif [[ "$up" == "1" ]]; then
            # Update to Master-Branch
            old_ver=$(git show --pretty="%H" -s HEAD)
            status_run 'Updating to latest codebase' 'git pull --quiet' ''
            new_ver=$(git show --pretty="%H" -s HEAD)
            if [ "$old_ver" != "$new_ver" ]; then
                status_run "Updated from $old_ver to $new_ver" ''
            fi
        elif [[ "$up" == "3" ]]; then
            # Update to last Tag
            old_ver=$(git describe --exact-match --tags $(git log -n1 --pretty='%h'))
            status_run 'Updating to latest release' 'git fetch --tags && git checkout $(git describe --tags $(git rev-list --tags --max-count=1))'
            new_ver=$(git describe --exact-match --tags $(git log -n1 --pretty='%h'))
            if [[ "$old_ver" != "$new_ver" ]]; then
                status_run "Updated from $old_ver to $new_ver" ''
            fi
        fi

        cnf=$(echo $(grep '\[.distributed_poller.\]' config.php | egrep -v -e '^//' -e '^#' | cut -d = -f 2 | sed 's/;//g'))
        if ((${BASH_VERSINFO[0]} < 4)); then
            cnf=`echo $cnf|tr [:upper:] [:lower:]`
        else
            cnf=${cnf,,}
        fi

        if [[ -z "$cnf" ]] || [[ "$cnf" == "0" ]] || [[ "$cnf" == "false" ]]; then
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
                local options=("refresh_alert_rules"
                               "syslog"
                               "eventlog"
                               "authlog"
                               "perf_time"
                               "callback"
                               "device_perf"
                               "purgeusers"
                               "bill_data"
                               "alert_log"
                               "rrd_purge");
                call_daily_php "${options[@]}";
            ;;
            submodules)
                # Init+Update our submodules
                git submodule --quiet init
                git submodule --quiet update
            ;;
            notifications)
                # Get notifications
                local options=("notifications");
                call_daily_php "${options[@]}";
            ;;
        esac
    fi
}

main "$@"
