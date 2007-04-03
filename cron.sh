#!/bin/bash

./poll-reachability.php
./poll-device.php &
./poll-interface.php &
./poll-temperatures.php &
./ips.php &
./check-services.php
./alerts.php

wget -O /var/sites/network.vostron.net/rrd/dill.vostron.net-mail_virus.rrd http://dill.vostron.net/rrd/mailgraph_virus.rrd
wget -O /var/sites/network.vostron.net/rrd/dill.vostron.net-mail.rrd http://dill.vostron.net/rrd/mailgraph.rrd
wget -O /var/sites/network.vostron.net/rrd/dill.vostron.net-courier.rrd http://dill.vostron.net/rrd/couriergraph.rrd

