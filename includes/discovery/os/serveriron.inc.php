<?php

if (str_contains('ServerIron', $sysDescr)) {
    $os = 'serveriron';
    $serviron_mibs = array (
        "snL4slbTotalConnections"       => "FOUNDRY-SN-SW-L4-SWITCH-GROUP-MIB",  // Total connections in this device
        "snL4slbLimitExceeds"           => "FOUNDRY-SN-SW-L4-SWITCH-GROUP-MIB",  // exceeds snL4TCPSynLimit (numbers of connection per second)
        "snL4slbForwardTraffic"         => "FOUNDRY-SN-SW-L4-SWITCH-GROUP-MIB",  // Client->Server
        "snL4slbReverseTraffic"         => "FOUNDRY-SN-SW-L4-SWITCH-GROUP-MIB",  // Server->Client
        "snL4slbFinished"               => "FOUNDRY-SN-SW-L4-SWITCH-GROUP-MIB",  // FIN_or_RST
        "snL4FreeSessionCount"          => "FOUNDRY-SN-SW-L4-SWITCH-GROUP-MIB",  // Maximum sessions - used sessions
        "snL4unsuccessfulConn"          => "FOUNDRY-SN-SW-L4-SWITCH-GROUP-MIB",  // Unsuccessfull connection
    );

    register_mibs($device, $serviron_mibs, "includes/discovery/os/serveriron.inc.php");
}
