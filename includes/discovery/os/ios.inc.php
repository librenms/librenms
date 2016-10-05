<?php

if (str_contains($sysDescr, array('Cisco Internetwork Operating System Software', 'IOS (tm)', 'Cisco IOS Software', 'Global Site Selector')) && !str_contains($sysDescr, array('IOS-XE', 'X86_64_LINUX_IOSD'))) {
    $os = 'ios';

    $extra_mibs = array(
        "ciscoAAASessionMIB" => "CISCO-AAA-SESSION-MIB",
    );
    register_mibs($device, $extra_mibs, "includes/discovery/os/ios.inc.php");
}
