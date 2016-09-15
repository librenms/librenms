<?php

if (!$os) {
    $apcDesc = array(
        'APC Web/SNMP Management Card',
        'APC Switched Rack PDU',
        'APC MasterSwitch PDU',
        'APC Metered Rack PDU',
    );

    if (str_contains($sysDescr, $apcDesc)) {
        $os = 'apc';
    }
}
