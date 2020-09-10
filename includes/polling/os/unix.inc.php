<?php

if (in_array($device['os'], array("linux", "endian", "proxmox", "recoveryos"))) {
    list(,,$version) = explode(" ", $device['sysDescr']);
    if (preg_match('/[3-6]86/', $device['sysDescr'])) {
        $hardware = "Generic x86";
    } elseif (strstr($device['sysDescr'], "x86_64")) {
        $hardware = "Generic x86 64-bit";
    } elseif (strstr($device['sysDescr'], "sparc32")) {
        $hardware = "Generic SPARC 32-bit";
    } elseif (strstr($device['sysDescr'], "sparc64")) {
        $hardware = "Generic SPARC 64-bit";
    } elseif (strstr($device['sysDescr'], "mips")) {
        $hardware = "Generic MIPS";
    } elseif (strstr($device['sysDescr'], "armv5") && $device['sysObjectID'] != '.1.3.6.1.4.1.674.10892.2') {
        // Except iDrac6 from being detected as armv5
        $hardware = "Generic ARMv5";
    } elseif (strstr($device['sysDescr'], "armv6")) {
        $hardware = "Generic ARMv6";
    } elseif (strstr($device['sysDescr'], "armv7")) {
        $hardware = "Generic ARMv7";
    } elseif (strstr($device['sysDescr'], "aarch64")) {
        $hardware = "Generic ARMv8 64-bit";
    } elseif (strstr($device['sysDescr'], "armv")) {
        $hardware = "Generic ARM";
    }
}
