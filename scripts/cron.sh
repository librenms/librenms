#!/bin/bash

#./discovery.php -h forced >> /var/log/observer.log &

./poller.php -h all >> /var/log/observer.log &

#./poller.php -h odd >> /var/log/observer.log &
#./poller.php -h even >> /var/log/observer.log &

#./alerts.php
./poll-billing.php
