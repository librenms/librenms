#!/bin/bash
./discovery.php --even &
./discovery.php --odd &
./update-interface.php
./discover-cdp.php
./cleanup.php
./generate-map.sh
./check-errors.php
./versioncheck.php --cron
