<?php
$serial = trim(snmp_get($device, '1.3.6.1.2.1.43.5.1.1.17.1', '-OQv', '', ''), '" ');
