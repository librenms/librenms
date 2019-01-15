<?php
use LibreNMS\RRD\RrdDefinition;

$cmstats = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.32285.11.1.1.2.2.3.1.0', '.1.3.6.1.4.1.32285.11.1.1.2.2.3.6.0', '.1.3.6.1.4.1.32285.11.1.1.2.2.3.5.0']);
$cmtotal      = $cmstats['.1.3.6.1.4.1.32285.11.1.1.2.2.3.1.0'];
$cmreg        = $cmstats['.1.3.6.1.4.1.32285.11.1.1.2.2.3.6.0'];
$cmoffline    = $cmstats['.1.3.6.1.4.1.32285.11.1.1.2.2.3.5.0'];

$serial = snmp_getnext($device, ".1.3.6.1.4.1.32285.11.1.1.2.1.1.1.16", "-OQv");
$hardware = snmp_getnext($device, ".1.3.6.1.4.1.32285.11.1.1.2.1.1.1.18", "-OQv");

if (is_numeric($cmtotal)) {
    $rrd_def = RrdDefinition::make()->addDataset('cmtotal', 'GAUGE', 0);

    $fields = array(
        'cmtotal' => $cmtotal,
    );

    $tags = compact('rrd_def');
    data_update($device, 'topvision_cmtotal', $tags, $fields);
    $graphs['topvision_cmtotal'] = true;
}

if (is_numeric($cmreg)) {
    $rrd_def = RrdDefinition::make()->addDataset('cmreg', 'GAUGE', 0);

    $fields = array(
        'cmreg' => $cmreg,
    );

    $tags = compact('rrd_def');
    data_update($device, 'topvision_cmreg', $tags, $fields);
    $graphs['topvision_cmreg'] = true;
}

if (is_numeric($cmoffline)) {
    $rrd_def = RrdDefinition::make()->addDataset('cmoffline', 'GAUGE', 0);

    $fields = array(
        'cmoffline' => $cmoffline,
    );

    $tags = compact('rrd_def');
    data_update($device, 'topvision_cmoffline', $tags, $fields);
    $graphs['topvision_cmoffline'] = true;
}
