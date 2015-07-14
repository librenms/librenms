<?php

if (!$os) {
    if (strstr($sysDescr, 'APC Web/SNMP Management Card')) {
        $os = 'apc';
    }
    if (strstr($sysDescr, 'APC Switched Rack PDU')) {
        $os = 'apc';
    }
    if (strstr($sysDescr, 'APC MasterSwitch PDU')) {
        $os = 'apc';
    }
    if (strstr($sysDescr, 'APC Metered Rack PDU')) {
        $os = 'apc';
    }
}
