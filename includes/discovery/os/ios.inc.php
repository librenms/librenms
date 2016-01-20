<?php

if (empty($os)) {
    if (strstr($sysDescr, 'Cisco Internetwork Operating System Software')) {
        $os = 'ios';
    }
    else if (strstr($sysDescr, 'IOS (tm)')) {
        $os = 'ios';
    }
    else if (strstr($sysDescr, 'Cisco IOS Software')) {
        $os = 'ios';
    }
    else if (strstr($sysDescr, 'Global Site Selector')) {
        $os = 'ios';
    }

    if (strstr($sysDescr, 'IOS-XE')) {
        $os = 'iosxe';
    }

    if (strstr($sysDescr, 'IOS XR')) {
        $os = 'iosxr';
    }

    if (!empty($os)) {
        $extra_mibs = array(
            "ciscoAAASessionMIB" => "CISCO-AAA-SESSION-MIB",
        );
        register_mibs($device, $extra_mibs, "includes/discovery/os/ios.inc.php");
    }
}

// Fallback case
// If we don't have an OS yet and if the object is in Cisco tree it's most likely an IOS device
// if (empty($os) and substr($sysObjectId, 0, 17) == ".1.3.6.1.4.1.9.1.") { $os = "ios"; }
