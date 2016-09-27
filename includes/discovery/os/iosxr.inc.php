<?php

if (str_contains($sysDescr, 'IOS XR')) {
    $os = 'iosxr';

    $extra_mibs = array(
        "ciscoAAASessionMIB" => "CISCO-AAA-SESSION-MIB",
    );
    register_mibs($device, $extra_mibs, "includes/discovery/os/iosxr.inc.php");
}
