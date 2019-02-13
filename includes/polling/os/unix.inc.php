<?php

if ($device['os'] == "linux" || $device['os'] == "endian" || $device['os'] == "proxmox") {
    list(,,$version) = explode(" ", $device['sysDescr']);
    if (strstr($device['sysDescr'], "386")|| strstr($device['sysDescr'], "486")||strstr($device['sysDescr'], "586")||strstr($device['sysDescr'], "686")) {
        $hardware = "Generic x86";
    } elseif (strstr($device['sysDescr'], "x86_64")) {
        $hardware = "Generic x86 64-bit";
    } elseif (strstr($device['sysDescr'], "sparc32")) {
        $hardware = "Generic SPARC 32-bit";
    } elseif (strstr($device['sysDescr'], "sparc64")) {
        $hardware = "Generic SPARC 64-bit";
    } elseif (strstr($device['sysDescr'], "mips")) {
        $hardware = "Generic MIPS";
    } // Except iDrac6 from being detected as armv5
    elseif (strstr($device['sysDescr'], "armv5") && $device['sysObjectID'] != '.1.3.6.1.4.1.674.10892.2') {
        $hardware = "Generic ARMv5";
    } elseif (strstr($device['sysDescr'], "armv6")) {
        $hardware = "Generic ARMv6";
    } elseif (strstr($device['sysDescr'], "armv7")) {
        $hardware = "Generic ARMv7";
    } elseif (strstr($device['sysDescr'], "armv")) {
        $hardware = "Generic ARM";
    }

    $features = snmp_get($device, 'nsExtendOutput1Line."distro"', '-Oqv', 'NET-SNMP-EXTEND-MIB');

    # Detect Dell hardware via OpenManage SNMP
    $hw = snmp_get($device, ".1.3.6.1.4.1.674.10892.1.300.10.1.9.1", "-Oqv", "MIB-Dell-10892");
    $hw = trim(str_replace("\"", "", $hw));
    if ($hw) {
        $hardware = "Dell " . $hw;
    } else {
        $hw = trim(snmp_get($device, 'cpqSiProductName.0', '-Oqv', 'CPQSINFO-MIB', 'hp'), '"');
        if (!empty($hw)) {
            $hardware = $hw;
        }
    }

    $serial = snmp_get($device, ".1.3.6.1.4.1.674.10892.1.300.10.1.11.1", "-Oqv", "MIB-Dell-10892");
    $serial = trim(str_replace("\"", "", $serial));

    # Use agent DMI data if available
    if (isset($agent_data['dmi'])) {
        if ($agent_data['dmi']['system-product-name']) {
            $hardware = ($agent_data['dmi']['system-manufacturer'] ? $agent_data['dmi']['system-manufacturer'] . ' ' : '') . $agent_data['dmi']['system-product-name'];

            # Clean up Generic hardware descriptions
            $hardware = rewrite_generic_hardware($hardware);
        }

        if ($agent_data['dmi']['system-serial-number']) {
            $serial = $agent_data['dmi']['system-serial-number'];
        }
    }
} elseif ($device['os'] == "freebsd") {
    $device['sysDescr'] = str_replace(" 0 ", " ", $device['sysDescr']);
    list(,,$version) = explode(" ", $device['sysDescr']);
    if (strstr($device['sysDescr'], "i386")) {
        $hardware = "i386";
    } elseif (strstr($device['sysDescr'], "amd64")) {
        $hardware = "amd64";
    } else {
        $hardware = "i386";
    }

    # Distro "extend" support
    $features = snmp_get($device, 'nsExtendOutput1Line."distro"', '-Oqv', 'NET-SNMP-EXTEND-MIB');

    if (!$features) {
        $features = 'GENERIC';
    }
} elseif ($device['os'] == "dragonfly") {
    list(,,$version,,,$features,,$hardware) = explode(" ", $device['sysDescr']);
} elseif ($device['os'] == "netbsd") {
    list(,,$version,,,$features) = explode(" ", $device['sysDescr']);
    $features = str_replace("(", "", $features);
    $features = str_replace(")", "", $features);
    list(,,$hardware) = explode("$features", $device['sysDescr']);
} elseif ($device['os'] == "solaris" || $device['os'] == "opensolaris") {
    list(,,$version,$features,$hardware) = explode(" ", $device['sysDescr']);
    $features = str_replace("(", "", $features);
    $features = str_replace(")", "", $features);
} elseif ($device['os'] == "monowall" || $device['os'] == "Voswall") {
    list(,,$version,$hardware,$freebsda, $freebsdb, $arch) = explode(" ", $device['sysDescr']);
    $features = $freebsda . " " . $freebsdb;
    $hardware = "$hardware ($arch)";
    $hardware = str_replace("\"", "", $hardware);
} elseif ($device['os'] == "qnap") {
    $hardware = snmp_get($device, "ENTITY-MIB::entPhysicalName.1", "-Osqnv");
    $version  = snmp_get($device, "ENTITY-MIB::entPhysicalFirmwareRev.1", "-Osqnv");
    $serial   = snmp_get($device, "ENTITY-MIB::entPhysicalSerial.1", "-Osqnv");
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
} elseif ($device['os'] == "pfsense") {
    $output = preg_split("/ /", $device['sysDescr']);
    $version = $output[2];
    $hardware = $output[6];
}

// snmp extend scripts
if (empty($hardware) || starts_with($hardware, 'Generic')) {
    # Try detect using the snmp extend option (dmidecode or /sys/devices/virtual/dmi)
    $hw = snmp_get($device, 'nsExtendOutput1Line."hardware"', '-Oqv', 'NET-SNMP-EXTEND-MIB');

    if ($hw) {
        $mfg = snmp_get($device, 'nsExtendOutput1Line."manufacturer"', '-Oqv', 'NET-SNMP-EXTEND-MIB');
        $hardware = trim("$mfg $hw");
    }
}

if (empty($serial)) {
    $serial = snmp_get($device, 'nsExtendOutput1Line."serial"', '-Oqv', 'NET-SNMP-EXTEND-MIB');
}
