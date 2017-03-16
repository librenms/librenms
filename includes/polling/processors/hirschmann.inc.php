<?php

$proc = snmp_get($device, "HMPRIV-MGMT-SNMP-MIB::hmCpuUtilization.0", "-OvqU");
