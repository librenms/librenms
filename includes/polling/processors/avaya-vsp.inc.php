<?php

echo 'Avaya VSP CPU Usage';

$proc = trim(snmp_get($device, "1.3.6.1.4.1.2272.1.85.10.1.1.2.1", "-Ovq"),'"');
