<?php

if (str_contains($sysDescr, 'EMC SOHO-NAS Storage.')) {
    $os = 'lenovoemc';
}

if (!empty($os)) {
    $lenovoemc_mibs = array(
        "fanValue"  => "IOMEGANAS-MIB",
        "tempValue"     => "IOMEGANAS-MIB",
        "raidStatus"    => "IOMEGANAS-MIB",
        "diskStatus"    => "IOMEGANAS-MIB"
    );
    register_mibs($device, $lenovoemc_mibs, "includes/discovery/os/lenovoemc.inc.php");
}
