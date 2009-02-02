#!/bin/bash

./poll-mac_accounting.php >> /var/log/observer.log &
./discover-bgp_peers.php >> /var/log/observer.log &
./poll-device.php --odd >> /var/log/observer.log &
./poll-device.php --even >> /var/log/observer.log &
./check-services.php
#./alerts.php
./poll-billing.php
