<?php

log_event('SNMP Trap: Authentication Failure: ' . format_hostname($device), $device, 'auth', 3, $device['hostname']);
