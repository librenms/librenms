<?php

// some sensors return data incorrectly appending a .0
$sensor_value = $snmp_data[$sensor['sensor_oid']] ?? $snmp_data[$sensor['sensor_oid'] . '.0'];
