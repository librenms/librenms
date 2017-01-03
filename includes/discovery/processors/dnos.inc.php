<?php
if ($device['os'] == 'dnos') {
    echo 'DNOS CPU: ';
    $get_series = explode('.', snmp_get($device, 'mib-2.1.2.0', '-Onvsbq', 'F10-PRODUCTS-MIB', 'dnos'), 2); // Get series From MIB
    $series = $get_series[0];
    $descr = 'CPU';

    if ($series == 'f10SSeriesProducts') {
        echo 'Dell S Series Chassis';
        $usage = trim(snmp_get($device, 'chStackUnitCpuUtil5Sec.1', '-OvQ', 'F10-S-SERIES-CHASSIS-MIB'));

        discover_processor($valid['processor'], $device, 'F10-S-SERIES-CHASSIS-MIB::chStackUnitCpuUtil5Sec.1', '0', 'dnos-cpu', '1', $usage, null, null);
    } elseif ($series == 'f10CSeriesProducts') {
         echo 'Dell C Series Chassis';
         $usage = trim(snmp_get($device, 'chLineCardCpuUtil5Sec.1', '-OvQ', 'F10-S-SERIES-CHASSIS-MIB'));

         discover_processor($valid['processor'], $device, 'F10-C-SERIES-CHASSIS-MIB::chLineCardCpuUtil5Sec.1', '0', 'dnos-cpu', '1', $usage, null, null);
    } else {
        preg_match('/(\d*\.\d*)/', snmp_get($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '-OvQ'), $matches);
        $usage = $matches[0];

        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.1.1.4.9.0', '0', 'dnos-cpu', $descr, '1', $usage, null, null);
    }
}
