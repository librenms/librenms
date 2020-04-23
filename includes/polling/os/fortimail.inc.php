<?php

use LibreNMS\RRD\RrdDefinition;

$fmlSysVersion = snmp_get($device, 'FORTINET-FORTIMAIL-MIB::fmlSysVersion.0', '-Ovq');
$serial       = snmp_get($device, 'ENTITY-MIB::entPhysicalSerialNum.1', '-Ovq');
$version                 = preg_replace('/(.+),(.+),(.+)/', '\\1||\\2||\\3', $fmlSysVersion);
list($version,$features) = explode('||', $version);
if (isset($rewrite_fortinet_hardware[$device['sysObjectID']])) {
    $hardware = $rewrite_fortinet_hardware[$device['sysObjectID']];
}
if (empty($hardware)) {
    $hardware = snmp_get($device, 'ENTITY-MIB::entPhysicalModelName.1', '-Ovq');
}

$sessions = snmp_get($device, 'FORTINET-FORTIMAIL-MIB::fmlSysSesCount.0', '-Ovq');
if (is_numeric($sessions)) {
    $rrd_def = RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0, 3000000);

    print "Sessions: $sessions\n";
    $fields = array(
        'sessions' => $sessions,
    );

    $tags = compact('rrd_def');
    data_update($device, 'fortimail_sessions', $tags, $fields);
    $graphs['fortimail_sessions'] = true;
}

$cpu_usage = snmp_get($device, 'FORTINET-FORTIMAIL-MIB::fmlSysCpuUsage.0', '-Ovq');
if (is_numeric($cpu_usage)) {
    $rrd_def = RrdDefinition::make()->addDataset('LOAD', 'GAUGE', -1, 100);

    echo "CPU: $cpu_usage%\n";
    $fields = array(
        'LOAD' => $cpu_usage,
    );

    $tags = compact('rrd_def');
    data_update($device, 'fortimail_cpu', $tags, $fields);
    $graphs['fortimail_cpu'] = true;
}
