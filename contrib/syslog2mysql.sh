#!/bin/bash

if [ ! -e /var/log/observernms-mysql.pipe ]
then
mkfifo /var/log/observernms-mysql.pipe
fi
while [ -e /var/log/observernms-mysql.pipe ]
do
mysql -u observernms --password=password observernms < /var/log/observernms-mysql.pipe >/dev/null
done 
