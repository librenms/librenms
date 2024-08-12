#!/bin/bash

if [ -w /var/run/php/php-fpm-librenms-poller.sock ]
then
    IGNORE=2

    EXIT_REGEX="lnms_exit_status:([0-9]+)"

    SCRIPT_FILENAME=/opt/librenms/lnms.php \
    REQUEST_METHOD=GET \
    QUERY_STRING="$*" \
    cgi-fcgi -bind -connect /var/run/php/php-fpm-librenms-poller.sock | while read -r line
    do
        if [ $IGNORE -gt 0 ]
        then
            IGNORE=$((IGNORE - 1))
            continue
        fi
        if [[ $line =~ $EXIT_REGEX ]]
        then
            exit "${BASH_REMATCH[1]}"
        fi
        echo "$line"
    done
else
    lnms "$@"
fi

exit $?
