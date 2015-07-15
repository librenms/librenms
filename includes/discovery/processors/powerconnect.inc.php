<?php
if ($device['os'] == "powerconnect")
{
    $sysObjectId = snmp_get ($device, "SNMPv2-MIB::sysObjectID.0", "-Ovqn");
    switch ($sysObjectId) {
        case ".1.3.6.1.4.1.674.10895.3031":
        /**
        * Devices supported:
        * Dell Powerconnect 55xx
        */
        $usage = trim(snmp_get($device, ".1.3.6.1.4.1.89.1.7.0", "-Ovq"));
        discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.89.1.7.0", "0", "powerconnect","Processor", "1", $usage, NULL, NULL);
        break;
        default :
        /**
         * Defaul Discovery for powerconnect series
         *  Dell-Vendor-MIB::dellLanExtension.6132.1.1.1.1.4.4.0 = STRING: "5 Sec (6.99%),    1 Min (6.72%),   5 Min (9.06%)"
         */
        $descr = "Processor";
        $usage = trim(snmp_get($device, "dellLanExtension.6132.1.1.1.1.4.4.0", "-OQUvs", "Dell-Vendor-MIB"),'"');
        if (substr($usage,0,5) == "5 Sec")
        {
            discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.4.0", "0", "powerconnect", $descr, "1", $usage, NULL, NULL);
        }

    }
}
