<?php

if (str_contains($sysDescr, array('IOS-XE', 'X86_64_LINUX_IOSD'))) {
    $os = 'iosxe';

    $extra_mibs = array(
        "ciscoAAASessionMIB" => "CISCO-AAA-SESSION-MIB",
    );
    register_mibs($device, $extra_mibs, "includes/discovery/os/iosxe.inc.php");
}
