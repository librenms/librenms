<?php

$apc_desc = array(
    'APC Web/SNMP Management Card',
    'APC Switched Rack PDU',
    'APC MasterSwitch PDU',
    'APC Metered Rack PDU',
);

if (str_contains($sysDescr, $apc_desc)) {
    $os = 'apc';
}

unset($apc_desc);
