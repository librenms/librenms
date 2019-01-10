<?php
$serial = snmp_getnext($device, ".1.3.6.1.4.1.32285.11.1.1.2.1.1.1.16", "-OQv");
$hardware = snmp_getnext($device, ".1.3.6.1.4.1.32285.11.1.1.2.1.1.1.18", "-OQv");
