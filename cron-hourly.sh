#!/bin/bash
./discover-cdp.php
./discover-ifs.php
./discover-nets.php
./ips.php
./cleanup.php
./discover-storage.php &
./discover-temperatures.php &
./generate-map.sh &
./discover-cisco-temp.php &
./discover-vlans.php &
./update-interface.php &
./check-errors.php &
