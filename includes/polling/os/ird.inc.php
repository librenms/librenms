<?php

$hardware = trim(snmp_get($device, '1.3.6.1.2.1.1.5.0', '-OQv'), '"');
