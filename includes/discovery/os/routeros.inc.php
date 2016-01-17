<?php

if (!$os) {
    // RouterOS <= 4
    // sysDescr.0 = STRING: router
    if ($sysDescr == 'router') {
        if (is_numeric(snmp_get($device, 'SNMPv2-SMI::enterprises.14988.1.1.4.3.0', '-Oqv', ''))) {
            $os = 'routeros';
        }
    }

    // Routeros >= 5
    // sysDescr.0 = STRING: RouterOS RB493AH
    if (preg_match('/^RouterOS/', $sysDescr)) {
        $os = 'routeros';
    }

    if ($sysDescr == 'RB260GS') {
        $os = 'routeros';
    }

    // poll Cisco AAA MIB
    $extra_mibs = array(
        "ciscoAAASessionMIB" => "CISCO-AAA-SESSION-MIB",
    );
    register_mibs($device, $extra_mibs, "includes/discovery/os/routeros.inc.php");
}
