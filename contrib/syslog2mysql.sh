#!/bin/bash

if [ ! -e /var/log/observer-mysql.pipe ]
then
mkfifo /var/log/observer-mysql.pipe
fi
while [ -e /var/log/observer-mysql.pipe ]
do
mysql -u observer --password=password observer < /var/log/observer-mysql.pipe >/dev/null
done 
