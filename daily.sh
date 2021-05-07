#!/usr/bin/env bash
################################################################################
# Copyright (C) 2015 Daniel Preussker, QuxLabs UG <preussker@quxlabs.com>
# Copyright (C) 2016 Layne "Gorian" Breitkreutz <Layne.Breitkreutz@thelenon.com>
# Copyright (C) 2017 Tony Murray <murraytony@gmail.com>
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
# along with this program.  If not, see <https://www.gnu.org/licenses/>.
################################################################################

#######################################
# CONSTANTS
#######################################
# define DAILY_SCRIPT as the full path to this script and LIBRENMS_DIR as the directory this script is in
DAILY_SCRIPT=$(readlink -f "$0")
LIBRENMS_DIR=$(dirname "$DAILY_SCRIPT")
COMPOSER="php ${LIBRENMS_DIR}/scripts/composer_wrapper.php --no-interaction"

# set log_file, using librenms 'log_dir' config setting, if set
# otherwise we default to <LibreNMS Install Directory>/logs
LOG_DIR=$(php -r "@include '${LIBRENMS_DIR}/config.php'; echo isset(\$config['log_dir']) ? \$config['log_dir'] : '${LIBRENMS_DIR}/logs';")

# get the librenms user
# shellcheck source=.env.example
source "${LIBRENMS_DIR}/.env"
LIBRENMS_USER="${LIBRENMS_USER:-librenms}"
LIBRENMS_USER_ID=$(id -u "$LIBRENMS_USER")

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
    local args arg_text arg_command arg_option log_file exit_code tmp log_file

    args=("$@")
    arg_text=$1
    arg_command=$2
    arg_option=$3
    log_file=${LOG_DIR}/daily.log

    # set log_file, using librenms $config['log_dir'], if set
    # otherwise we default to ./logs/daily.log

    printf "%-50s" "${arg_text}"
    echo "${arg_text}" >> "${log_file}"
    tmp=$(bash -c "${arg_command}" 2>&1)
    exit_code=$?
    echo "${tmp}" >> "${log_file}"
    echo "Returned: ${exit_code}" >> "${log_file}"

    # print OK if the command ran successfully
    # or FAIL otherwise (non-zero exit code)
    if [[ "${exit_code}" == "0" ]]; then
        printf " \\033[0;32mOK\\033[0m\\n"
    else
        printf " \\033[0;31mFAIL\\033[0m\\n"
        if [[ "${arg_option}" == "update" ]]; then
            php "${LIBRENMS_DIR}/daily.php" -f notify -o "${tmp}"
        fi
        if [[ -n "${tmp}" ]]; then
            # print output in case of failure
            echo "${tmp}"
        fi
    fi
    return ${exit_code}
}

#######################################
# Call daily.php
# Globals:
#   LIBRENMS_DIR
# Arguments:
#   args:
#        Array of arguments to pass to
#        daily.php
# Returns:
#   Exit-Code of Command
#######################################
call_daily_php() {
    local args

    args=("$@")

    for arg in "${args[@]}"; do
        php "${LIBRENMS_DIR}/daily.php" -f "${arg}"
    done
}

#######################################
# Send result of a notifiable process to php code for processing
# Globals:
#   LIBRENMS_DIR
# Arguments:
#   args:
#        Type: update
#        Result: 1 for success, 0 for failure
# Returns:
#   Exit-Code of Command
#######################################
set_notifiable_result() {
    local args arg_type arg_result

    args=("$@")
    arg_type=$1
    arg_result=$2

    php "${LIBRENMS_DIR}/daily.php" -f handle_notifiable -t "${arg_type}" -r "${arg_result}"
}

#######################################
# Check the PHP and Python version and branch and switch to the appropriate branch
# Returns:
#   Exit-Code: 0 >= min ver, 1 < min ver
#######################################
check_dependencies() {
    local branch ver_56 ver_71 ver_72 ver_73 python3 python_deps phpver pythonver old_branches msg

    branch=$(git rev-parse --abbrev-ref HEAD)
    scripts/check_requirements.py > /dev/null 2>&1 || pip3 install -r requirements.txt > /dev/null 2>&1

    ver_56=$(php -r "echo (int)version_compare(PHP_VERSION, '5.6.4', '<');")
    ver_71=$(php -r "echo (int)version_compare(PHP_VERSION, '7.1.3', '<');")
    ver_72=$(php -r "echo (int)version_compare(PHP_VERSION, '7.2.5', '<');")
    ver_73=$(php -r "echo (int)version_compare(PHP_VERSION, '7.3', '<');")
    python3=$(python3 -c "import sys;print(int(sys.version_info < (3, 4)))" 2> /dev/null)
    python_deps=$("${LIBRENMS_DIR}/scripts/check_requirements.py" > /dev/null 2>&1; echo $?)
    phpver="master"
    pythonver="master"

    old_branches="^(php53|php56|php71-python2|php72)$"
    if [[ $branch =~ $old_branches ]] && [[ "$ver_73" == "0" && "$python3" == "0" && "$python_deps" == "0" ]]; then
        status_run "Supported PHP and Python version, switched back to master branch." 'git checkout master'
    elif [[ "$ver_56" != "0" ]]; then
        phpver="php53"
        if [[ "$branch" != "php53" ]]; then
            status_run "Unsupported PHP version, switched to php53 branch." 'git checkout php53'
        fi
    elif [[ "$ver_71" != "0" ]]; then
        phpver="php56"
        if [[ "$branch" != "php56" ]]; then
            status_run "Unsupported PHP version, switched to php56 branch." 'git checkout php56'
        fi
    elif [[ "$ver_72" != "0" || "$python3" != "0" || "$python_deps" != "0" ]]; then
        msg=""
        if [[ "$ver_72" != "0" ]]; then
            msg="Unsupported PHP version, $msg"
            phpver="php71"
        fi
        if [[ "$python3" != "0" ]]; then
            msg="python3 is not available, $msg"
            pythonver="python3-missing"
        elif [[ "$python_deps" != "0" ]]; then
            msg="Python 3 dependencies missing, $msg"
            pythonver="python3-deps"
        fi

        if [[ "$branch" != "php71-python2" ]]; then
            status_run "${msg}switched to php71-python2 branch." 'git checkout php71-python2'
        fi
    elif [[ "$ver_73" != "0" ]]; then
        phpver="php72"
        if [[ "$branch" != "php72" ]]; then
            status_run "Unsupported PHP version, switched to php72 branch." 'git checkout php72'
        fi
    fi

    set_notifiable_result phpver ${phpver}
    set_notifiable_result pythonver ${pythonver}

    if [[ "$phpver" == "master" && "$pythonver" == "master" ]]; then
        return 0
    fi
    return 1
}

#######################################
# Compare two numeric versions
# Arguments:
#   args:
#        version 1
#        version 2
#        parts: Number of parts to compare, from the left, compares all if unspecified
# Returns:
#   Exit-Code: 0: if equal 1: if 1 > 2  2: if 1 < 2
#######################################
version_compare () {
    local i ver1 ver2 parts1 parts2

    if [[ "$1" == "$2" ]]; then
        return 0
    fi

    IFS=. read -ra ver1 <<< "$1"
    IFS=. read -ra ver2 <<< "$2"

    parts2=${#ver2[@]}
    [[ -n $3 ]] && parts2=$3

    # fill empty fields in ver1 with zeros
    for ((i=${#ver1[@]}; i<parts2; i++)); do
        ver1[i]=0
    done

    parts1=${#ver1[@]}
    [[ -n $3 ]] && parts1=$3

    for ((i=0; i<parts1; i++)); do
        if [[ -z ${ver2[i]} ]]; then
            # fill empty fields in ver2 with zeros
            ver2[i]=0
        fi
        if ((10#${ver1[i]} > 10#${ver2[i]})); then
            return 1
        fi
        if ((10#${ver1[i]} < 10#${ver2[i]})); then
            return 2
        fi
    done
    return 0
}


#######################################
# Entry into program
# Globals:
#   LIBRENMS_DIR
# Arguments:
#
# Returns:
#   Exit-Code of Command
#######################################
main () {
    local arg old_version new_version branch options

    arg="$1"
    old_version="$2"
    new_version="$3"
    old_version="${old_version:=unset}"  # if $1 is unset, make it mismatch for pre-update daily.sh

    cd "${LIBRENMS_DIR}" || exit 1

    # if not running as $LIBRENMS_USER (unless $LIBRENMS_USER = root), relaunch
    if [[ "$LIBRENMS_USER" != "root" ]]; then
        # only try to su if we are root (or sudo)
        if [[ "$EUID" -eq 0 ]]; then
            echo "Re-running ${DAILY_SCRIPT} as ${LIBRENMS_USER} user"
            sudo -u "$LIBRENMS_USER" "$DAILY_SCRIPT" "$@"
            exit
        fi

        if [[ "$EUID" -ne "$LIBRENMS_USER_ID" ]]; then
            printf "\\033[0;93mWARNING\\033[0m: You should run this script as %s\\n" "${LIBRENMS_USER}"
        fi
    fi

    # make sure autoload.php exists before trying to run any php that may require it
    if [ ! -f "${LIBRENMS_DIR}/vendor/autoload.php" ]; then
        ${COMPOSER} install --no-dev
    fi

    if [[ -z "$arg" ]]; then
        up=$(php daily.php -f update >&2; echo $?)
        if [[ "$up" == "0" ]]; then
            ${DAILY_SCRIPT} no-code-update
            set_notifiable_result update 1  # make sure there are no update notifications if update is disabled
            exit
        fi

        check_dependencies
        php_ver_ret=$?

        # make sure the vendor directory is clean
        git checkout vendor/ --quiet > /dev/null 2>&1

        update_res=0
        if [[ "$up" == "1" ]] || [[ "$php_ver_ret" == "1" ]]; then
            # Update current branch to latest
            branch=$(git rev-parse --abbrev-ref HEAD)
            if [[ "$branch" == "HEAD" ]]; then
                # if the branch is HEAD, then we are not on a branch, checkout master
                git checkout master
            fi

            old_ver=$(git rev-parse --short HEAD)
            status_run 'Updating to latest codebase' 'git pull --quiet' 'update'
            update_res=$?
            new_ver=$(git rev-parse --short HEAD)
        else
            # Update to last Tag
            old_ver=$(git describe --exact-match --tags "$(git log -n1 --pretty='%h')" 2> /dev/null)

            # fetch new tags
            status_run 'Fetching new release information' "git fetch --tags" 'update'

            # collect versions full, base, new tag and hash
            IFS='-' read -ra full_version <<< "$(git describe --tags 2>/dev/null)"
            base_ver="${full_version[0]}"
            latest_hash=$(git rev-list --tags --max-count=1)
            latest_tag=$(git describe --exact-match --tags "${latest_hash}")

            #compare current base and latest version numbers (only the first two sections)
            version_compare "$base_ver" "$latest_tag" 2
            newer_check=$?

            if [[ -z $old_ver ]] && [[ $newer_check -eq 0 ]]; then
                echo 'Between releases, waiting for newer release'
            else
                status_run 'Updating to latest release' "git checkout ${latest_hash}" 'update'
                update_res=$?
                new_ver=$(git describe --exact-match --tags "$(git log -n1 --pretty='%h')")
            fi
        fi

        if (( update_res > 0 )); then
            set_notifiable_result update 0
        fi

        # Call ourself again in case above pull changed or added something to daily.sh
        ${DAILY_SCRIPT} post-pull "${old_ver}" "${new_ver}"
    else
        case $arg in
            no-code-update)
                # Updates of the code are disabled, just check for schema updates
                # and clean up the db.
                status_run 'Updating SQL-Schema' 'php includes/sql-schema/update.php'
                status_run 'Cleaning up DB' "$DAILY_SCRIPT cleanup"
            ;;
            post-pull)
                # re-check dependencies after pull with the new code
                check_dependencies

                # Check for missing vendor dir
                if [ ! -f vendor/autoload.php ]; then
                    git checkout 609676a9f8d72da081c61f82967e1d16defc0c4e -- vendor/
                    git reset HEAD vendor/  # don't add vendor directory to the index
                fi

                status_run 'Updating Composer packages' "${COMPOSER} install --no-dev" 'update'

                # Check if we need to revert (Must be in post pull so we can update it)
                if [[ "$old_version" != "$new_version" ]]; then
                    check_dependencies # check php and python version and switch branches

                    # new_version may be incorrect if we just switch branches... ignoring that detail
                    status_run "Updated from $old_version to $new_version" ''
                    set_notifiable_result update 1  # only clear the error if update was a success
                fi

                # List all tasks to do after pull in the order of execution
                status_run 'Updating SQL-Schema' 'php includes/sql-schema/update.php'
                status_run 'Updating submodules' "$DAILY_SCRIPT submodules"
                status_run 'Cleaning up DB' "$DAILY_SCRIPT cleanup"
                status_run 'Fetching notifications' "$DAILY_SCRIPT notifications"
                status_run 'Caching PeeringDB data' "$DAILY_SCRIPT peeringdb"
                status_run 'Caching Mac OUI data' "$DAILY_SCRIPT mac_oui"
            ;;
            cleanup)
                # Cleanups
                options=("refresh_alert_rules"
                               "refresh_os_cache"
                               "refresh_device_groups"
                               "recalculate_device_dependencies"
                               "syslog"
                               "eventlog"
                               "authlog"
                               "callback"
                               "device_perf"
                               "purgeusers"
                               "bill_data"
                               "alert_log"
                               "rrd_purge"
                               "ports_fdb"
                               "route"
                               "ports_purge")
                call_daily_php "${options[@]}"
            ;;
            submodules)
                # Init+Update our submodules
                git submodule --quiet init
                git submodule --quiet update
            ;;
            notifications)
                # Get notifications
                options=("notifications")
                call_daily_php "${options[@]}"
            ;;
            peeringdb)
                options=("peeringdb")
                call_daily_php "${options[@]}"
            ;;
            mac_oui)
                options=("mac_oui")
                call_daily_php "${options[@]}"
        esac
    fi
}

main "$@"
