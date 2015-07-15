<?php

$proc = substr(snmp_get($device, 'swCpuUsage.0', '-Ovq', 'DMswitch-MIB'), 0, 2);
