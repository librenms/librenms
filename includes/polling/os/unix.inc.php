<?php

if ($device['os'] == "linux" || $device['os'] == "endian") {
    list(,,$version) = explode (" ", $poll_device['sysDescr']);
    if (strstr($poll_device['sysDescr'], "386")|| strstr($poll_device['sysDescr'], "486")||strstr($poll_device['sysDescr'], "586")||strstr($poll_device['sysDescr'], "686")) {
        $hardware = "Generic x86";
    }
    else if (strstr($poll_device['sysDescr'], "x86_64")) {
        $hardware = "Generic x86 64-bit";
    }
    else if (strstr($poll_device['sysDescr'], "sparc32")) {
        $hardware = "Generic SPARC 32-bit";
    }
    else if (strstr($poll_device['sysDescr'], "sparc64")) {
        $hardware = "Generic SPARC 64-bit";
    }
    else if (strstr($poll_device['sysDescr'], "mips")) {
        $hardware = "Generic MIPS";
    }
    // Except iDrac6 from being detected as armv5
    else if (strstr($poll_device['sysDescr'], "armv5") && $poll_device['sysObjectID'] != '.1.3.6.1.4.1.674.10892.2') {
        $hardware = "Generic ARMv5";
    }
    else if (strstr($poll_device['sysDescr'], "armv6")) {
        $hardware = "Generic ARMv6";
    }
    else if (strstr($poll_device['sysDescr'], "armv7")) {
        $hardware = "Generic ARMv7";
    }
    else if (strstr($poll_device['sysDescr'], "armv")) {
        $hardware = "Generic ARM";
    }

    # Distro "extend" support

    # NET-SNMP-EXTEND-MIB::nsExtendOutput1Line.\"distro\"
    $features = str_replace("\"", "", snmp_get($device, ".1.3.6.1.4.1.8072.1.3.2.3.1.1.6.100.105.115.116.114.111", "-Oqv", "NET-SNMP-EXTEND-MIB"));

    if (!$features) { # No "extend" support, try legacy UCD-MIB shell support
        $features = str_replace("\"", "", snmp_get($device, ".1.3.6.1.4.1.2021.7890.1.3.1.1.6.100.105.115.116.114.111", "-Oqv", "UCD-SNMP-MIB"));
    }

    if (!$features) { # No "extend" support, try "exec" support
        $features = str_replace("\"", "", snmp_get($device, ".1.3.6.1.4.1.2021.7890.1.101.1", "-Oqv", "UCD-SNMP-MIB"));
    }

    # Detect Dell hardware via OpenManage SNMP
    $hw = snmp_get($device, ".1.3.6.1.4.1.674.10892.1.300.10.1.9.1", "-Oqv", "MIB-Dell-10892");
    $hw = trim(str_replace("\"", "", $hw));
    if ($hw) {
        $hardware = "Dell " . $hw;
    }

    $serial = snmp_get($device, ".1.3.6.1.4.1.674.10892.1.300.10.1.11.1", "-Oqv", "MIB-Dell-10892");
    $serial = trim(str_replace("\"", "", $serial));

    # Use agent DMI data if available
    if (isset($agent_data['dmi'])) {
        if ($agent_data['dmi']['system-product-name']) {
            $hardware = ($agent_data['dmi']['system-manufacturer'] ? $agent_data['dmi']['system-manufacturer'] . ' ' : '') . $agent_data['dmi']['system-product-name'];

            # Clean up "Dell Computer Corporation" and "Intel Corporation"
            $hardware = str_replace(" Computer Corporation","",$hardware);
            $hardware = str_replace(" Corporation","",$hardware);
        }

        if ($agent_data['dmi']['system-serial-number']) {
            $serial = $agent_data['dmi']['system-serial-number'];
        }
    }

}
elseif ($device['os'] == "freebsd") {
    $poll_device['sysDescr'] = str_replace(" 0 ", " ", $poll_device['sysDescr']);
    list(,,$version) = explode (" ", $poll_device['sysDescr']);
    if (strstr($poll_device['sysDescr'], "i386")) {
        $hardware = "i386";
    }
    else if (strstr($poll_device['sysDescr'], "amd64")) {
        $hardware = "amd64";
    }
    else {
        $hardware = "i386";
    }

    # Distro "extend" support

    # NET-SNMP-EXTEND-MIB::nsExtendOutput1Line.\"distro\"
    $features = str_replace("\"", "", snmp_get($device, ".1.3.6.1.4.1.8072.1.3.2.3.1.1.6.100.105.115.116.114.111", "-Oqv", "NET-SNMP-EXTEND-MIB"));

    if (!$features) { # No "extend" support, try legacy UCD-MIB shell support
        $features = str_replace("\"", "", snmp_get($device, ".1.3.6.1.4.1.2021.7890.1.3.1.1.6.100.105.115.116.114.111", "-Oqv", "UCD-SNMP-MIB"));
    }

    if (!$features) { # No "extend" support, try "exec" support
        $features = str_replace("\"", "", snmp_get($device, ".1.3.6.1.4.1.2021.7890.1.101.1", "-Oqv", "UCD-SNMP-MIB"));
    }

    if (!$features) {
        $features = 'GENERIC';
    }
}
elseif ($device['os'] == "dragonfly") {
    list(,,$version,,,$features,,$hardware) = explode (" ", $poll_device['sysDescr']);
}
elseif ($device['os'] == "netbsd") {
    list(,,$version,,,$features) = explode (" ", $poll_device['sysDescr']);
    $features = str_replace("(", "", $features);
    $features = str_replace(")", "", $features);
    list(,,$hardware) = explode ("$features", $poll_device['sysDescr']);
}
elseif ($device['os'] == "openbsd" || $device['os'] == "solaris" || $device['os'] == "opensolaris") {
    list(,,$version,$features,$hardware) = explode (" ", $poll_device['sysDescr']);
    $features = str_replace("(", "", $features);
    $features = str_replace(")", "", $features);
}
elseif ($device['os'] == "monowall" || $device['os'] == "Voswall") {
    list(,,$version,$hardware,$freebsda, $freebsdb, $arch) = explode(" ", $poll_device['sysDescr']);
    $features = $freebsda . " " . $freebsdb;
    $hardware = "$hardware ($arch)";
    $hardware = str_replace("\"", "", $hardware);
}
elseif ($device['os'] == "qnap") {
    $hardware = snmp_get($device, "ENTITY-MIB::entPhysicalName.1", "-Osqnv");
    $version  = snmp_get($device, "ENTITY-MIB::entPhysicalFirmwareRev.1", "-Osqnv");
    $serial   = snmp_get($device, "ENTITY-MIB::entPhysicalSerial.1", "-Osqnv");
}
elseif ($device['os'] == "dsm") {
    #  This only gets us the build, not the actual version number, so won't use this.. yet.
    #  list(,,,$version,) = explode(" ",$poll_device['sysDescr'],5);
    #  $version = "Build " . trim($version,'#');

    $hrSystemInitialLoadParameters = trim(snmp_get($device, "hrSystemInitialLoadParameters.0", "-Osqnv"));

    $options = explode(" ",$hrSystemInitialLoadParameters);

    foreach ($options as $option) {
        list($key,$value) = explode("=",$option,2);
        if ($key == "syno_hw_version") {
            $hardware = $value;
        }
    }

    $version = snmp_get($device, "version.0", "-Osqnv", "SYNOLOGY-SYSTEM-MIB");

}
elseif ($device['os'] == "pfsense") {
    $output = preg_split("/ /", $poll_device['sysDescr']);
    $version = $output[2];
    $hardware = $output[6];
}
