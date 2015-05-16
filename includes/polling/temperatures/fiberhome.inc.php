<?php
$sensor_value = snmp_get($device, "sysTemperature.0", "-Oqv", "GEPON-OLT-COMMON-MIB");

