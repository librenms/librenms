<?php

print_r(snmpwalk_cache_oid($device, 'system', array()));

print_r(snmp_cache_oid('system', $device, array()));
