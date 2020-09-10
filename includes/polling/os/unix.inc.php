<?php

if (in_array($device['os'], array("linux", "endian", "proxmox", "recoveryos"))) {
    //
} elseif ($device['os'] == "dsm") {
    #  This only gets us the build, not the actual version number, so won't use this.. yet.
    #  list(,,,$version,) = explode(" ",$device['sysDescr'],5);
    #  $version = "Build " . trim($version,'#');

    $hrSystemInitialLoadParameters = trim(snmp_get($device, "hrSystemInitialLoadParameters.0", "-Osqnv"));

    $options = explode(" ", $hrSystemInitialLoadParameters);

    foreach ($options as $option) {
        list($key,$value) = explode("=", $option, 2);
        if ($key == "syno_hw_version") {
            $hardware = $value;
        }
    }

    $version = snmp_get($device, "version.0", "-Osqnv", "SYNOLOGY-SYSTEM-MIB");
}
