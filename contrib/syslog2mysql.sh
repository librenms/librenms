#!/bin/sh

MYSQLDB=observium
MYSQLUSER=syslog
MYSQLPASS=<something>
PIPE=/var/log/mysql-observium.pipe


if [ -e $PIPE ]; then
        while [ -e $PIPE ]
                do
                        mysql -u$MYSQLUSER --password=$MYSQLPASS $MYSQLDB < $PIPE
        done
else
        mkfifo $PIPE
fi
