#!/bin/bash
./discovery.php -h even &
./discovery.php -h odd &
./generate-map.sh
./check-errors.php
./versioncheck.php --cron

