<?php

$version = trim(snmp_get($device, '1.3.6.1.4.1.674.10893.2.102.3.1.1.9.1', '-OQv', '', ''), '"');
