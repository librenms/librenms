<?php

$version = end(explode(' ', trim(snmp_get($device, "sysDescr.0", "-OQv", "SNMPv2-MIB"))));


