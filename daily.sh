#!/bin/bash

if [ $(php daily.php -f update) -eq 1 ]; then 
  git pull --no-edit --quiet
fi

php daily.php -f syslog
php daily.php -f eventlog
