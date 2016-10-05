<?php

if (str_contains(snmp_get($device, 'ENTITY-MIB::entPhysicalDescr.1', '-Oqvn'), 'Avaya IP Office')) {
    $os = "avaya-ipo";
}
