#!/bin/bash

#./discovery.php -h forced >> /var/log/observer.log &

./poll-device.php -h all >> /var/log/observer.log &

#./poll-device.php -h odd >> /var/log/observer.log &
#./poll-device.php -h even >> /var/log/observer.log &

#./alerts.php
./poll-billing.php
