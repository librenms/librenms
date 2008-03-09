#!/bin/bash

#./poll-reachability.php
./poll-device.php --odd >> /var/log/observer.log &
./poll-device.php --even >> /var/log/observer.log &
#./ips.php &
./check-services.php
#./alerts.php

./poll-billing.php
