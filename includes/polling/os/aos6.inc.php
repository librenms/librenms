<?php
if (strpos($device['sysDescr'], 'Enterprise')) {
    list(,,$hardware,$version) = explode(' ', $device['sysDescr']);
} elseif (strpos($device['sysObjectID'], ".1.3.6.1.4.1.6486.800.1.1.2.1.10") !== false) {
    preg_match('/deviceOmniSwitch(....)(.+)/', snmp_get($device, 'sysObjectID.0', '-Osqv', 'ALCATEL-IND1-DEVICES:SNMPv2-MIB'), $model); // deviceOmniSwitch6400P24
    list($hardware,$version,) = explode(' ', 'OS'.$model[1].'-'.$model[2].' ' . $device['sysDescr']);
} else {
    list(,$hardware,$version) = explode(' ', $device['sysDescr']);
}
