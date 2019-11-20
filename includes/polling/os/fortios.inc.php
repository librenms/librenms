<?php

use LibreNMS\RRD\RrdDefinition;

$temp_data = snmp_get_multi_oid($device, ['fnSysSerial.0', 'fmSysVersion.0', 'fmDeviceEntMode.1'], '-OUQs', 'FORTINET-CORE-MIB:FORTINET-FORTIMANAGER-FORTIANALYZER-MIB');
$serial = $temp_data['fnSysSerial.0'];
$version = $temp_data['fmSysVersion.0'];
$hardware = rewrite_fortinet_hardware($device['sysObjectID']);
if ($hardware == $device['sysObjectID']) {
    unset($hardware);
}

//Log rate only for FortiAnalyzer features enabled FortiManagers
if ($temp_data['fmDeviceEntMode.1'] == 'fmg-faz') {
    $features = 'with Analyzer features';
    $log_rate = snmp_get($device, '.1.3.6.1.4.1.12356.103.2.1.9.0', '-Ovq');
    $log_rate = str_replace(' logs per second', '', $log_rate);
    $rrd_def = RrdDefinition::make()->addDataset('lograte', 'GAUGE', 0, 100000000);
    $fields = array(
        'lograte' => $log_rate,
    );
    $tags = compact('rrd_def');
    data_update($device, 'fortios_lograte', $tags, $fields);
    $graphs['fortios_lograte'] = true;
}
unset($temp_data);
