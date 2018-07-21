<?php

log_event('SNMP Trap: Authentication Failure: ' . $device['sysName'], $device, 'auth', 3, $device['hostname']);
