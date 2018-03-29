<?php
/**
 * Management Card(s) Memory usage
 */
if ($device['os'] == 'fiberhome') {
    /*
     * Check if Card is installed
     */
    $card1Status = snmp_get($device, 'mgrCardWorkStatus.9', '-Ovq', 'GEPON-OLT-COMMON-MIB');
    $card2Status = snmp_get($device, 'mgrCardWorkStatus.10', '-Ovq', 'GEPON-OLT-COMMON-MIB');
    if ($card1Status == '1') {
        $usage = snmp_get($device, 'mgrCardMemUtil.9', '-Ovq', 'GEPON-OLT-COMMON-MIB');
        discover_mempool($valid_mempool, $device, 9, 'fiberhome', 'Hswa 9 Memory', '100', null, null);
    };
    if ($card2Status == '1') {
        $usage = snmp_get($device, 'mgrCardMemUtil.10', '-Ovq', 'GEPON-OLT-COMMON-MIB');
        discover_mempool($valid_mempool, $device, 10, 'fiberhome', 'Hswa 10 Memory', '100', null, null);
    };
}
