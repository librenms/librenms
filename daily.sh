#!/bin/bash

cd $(dirname $0) || exit 1

if [ $(php daily.php -f update) -eq 1 ]; then 
  git pull --quiet
  php includes/sql-schema/update.php
fi

php daily.php -f syslog
php daily.php -f eventlog
php daily.php -f authlog
