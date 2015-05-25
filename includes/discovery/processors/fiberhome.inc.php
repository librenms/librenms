<?php
/**
* Management Card(s) CPU usage
*
*/
if ($device['os'] == "fiberhome") {
    /**
     * Check if Card is installed
     */
    $card1Status = snmp_get($device, "mgrCardWorkStatus.9", "-Ovq", "GEPON-OLT-COMMON-MIB");
    $card2Status = snmp_get($device, "mgrCardWorkStatus.10", "-Ovq", "GEPON-OLT-COMMON-MIB");
    if($card1Status == '1'){
        $usage = snmp_get($device, "mgrCardCpuUtil.9", "-Ovq", "GEPON-OLT-COMMON-MIB");
        discover_processor($valid['processor'], $device, "1.3.6.1.4.1.5875.800.3.9.8.1.1.5.9", "0", "fiberhome", "Hswa 9 Processor", "100", $usage/100, NULL, NULL);
    };
    if($card2Status == '1'){
        $usage = snmp_get($device, "mgrCardCpuUtil.10", "-Ovq", "GEPON-OLT-COMMON-MIB");
        discover_processor($valid['processor'], $device, "1.3.6.1.4.1.5875.800.3.9.8.1.1.5.10", "1", "fiberhome", "Hswa 10 Processor", "100", $usage/100, NULL, NULL);
    };
}
