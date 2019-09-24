<?php

if (in_array($device['os'], array("linux", "endian", "proxmox", "recoveryos"))) {
    list(,,$version) = explode(" ", $device['sysDescr']);
    if (preg_match('[3-6]86', $device['sysDescr'])) {
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
    } else {
        $hw = trim(snmp_get($device, 'cpqSiProductName.0', '-Oqv', 'CPQSINFO-MIB', 'hp'), '"');
        if (!empty($hw)) {
            $hardware = $hw;
        }
    }

    if (empty($hw)) {
    # Try detect using the extended option (dmidecode)
        $version_dmide = str_replace("\"", "", snmp_get($device, ".1.3.6.1.4.1.2021.7890.2.4.1.2.8.104.97.114.100.119.97.114.101.1", "-Oqv"));
        if (!$version_dmide) { # No "extend" support, try "exec" support
            $version_dmide = str_replace("\"", "", snmp_get($device, ".1.3.6.1.4.1.2021.7890.2.101.1", "-Oqv"));
        }
        $version_dmide = trim(str_replace("\"", "", $version_dmide));

        $hardware_dmide = str_replace("\"", "", snmp_get($device, ".1.3.6.1.4.1.2021.7890.3.4.1.2.12.109.97.110.117.102.97.99.116.117.114.101.114.1", "-Oqv"));
        if (!$hardware_dmide) { # No "extend" support, try "exec" support
            $hardware_dmide = str_replace("\"", "", snmp_get($device, ".1.3.6.1.4.1.2021.7890.3.101.1", "-Oqv"));
        }
        $hardware_dmide = trim(str_replace("\"", "", $hardware_dmide));
        if ($hardware_dmide) {
            $hardware = $hardware_dmide;
            if ($version_dmide) {
                $hardware = $hardware . " [" . $version_dmide . "]";
            }
        }
    }

    $serial = snmp_get($device, ".1.3.6.1.4.1.674.10892.1.300.10.1.11.1", "-Oqv", "MIB-Dell-10892");
    $serial = trim(str_replace("\"", "", $serial));

    # Try detect using the SNMP Extend option (dmidecode)
    if (!$serial) {
        $serial = str_replace("\"", "", snmp_get($device, ".1.3.6.1.4.1.2021.7890.4.4.1.2.6.115.101.114.105.97.108.1", "-Oqv"));
        $serial = trim(str_replace("\"", "", $serial));
    }

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
} elseif ($device['os'] == "aix") {
    $aix_descr = explode("\n", $device['sysDescr']);
    # AIX standard snmp deamon
    if ($aix_descr[1]) {
        $serial = explode("Processor id: ", $aix_descr[1])[1];
        $aix_long_version = explode(" version: ", $aix_descr[2])[1];
        list($version,$aix_version_min) = array_map('intval', explode(".", $aix_long_version));
    # AIX net-snmp
    } else {
        list(,,$aix_version_min,$version,$serial) = explode(" ", $aix_descr[0]);
    }
    $version .= "." . $aix_version_min;
}
