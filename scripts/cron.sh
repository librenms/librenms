#!/bin/bash

#./discovery.php --forced >> /var/log/observer.log

./poll-device.php --all >> /var/log/observer.log &
#./poll-device.php --even >> /var/log/observer.log &

#./poll-device.php --odd3 >> /var/log/observer.log &
#./poll-device.php --even3 >> /var/log/observer.log &
#./poll-device.php --other3 >> /var/log/observer.log &

./poll-mac_accounting.php >> /var/log/observer.log
./check-services.php
#./alerts.php
./poll-billing.php
