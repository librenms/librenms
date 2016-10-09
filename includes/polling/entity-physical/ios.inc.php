<?php

echo "Cisco Cat6xxx/76xx Crossbar : \n";

$mod_stats  = snmpwalk_cache_oid($device, 'cc6kxbarModuleModeTable', array(), 'CISCO-CAT6K-CROSSBAR-MIB');
$chan_stats = snmpwalk_cache_oid($device, 'cc6kxbarModuleChannelTable', array(), 'CISCO-CAT6K-CROSSBAR-MIB');
$chan_stats = snmpwalk_cache_oid($device, 'cc6kxbarStatisticsTable', $chan_stats, 'CISCO-CAT6K-CROSSBAR-MIB');

foreach ($mod_stats as $index => $entry) {
    $group = 'c6kxbar';
    foreach ($entry as $key => $value) {
        $subindex = null;
        $entPhysical_state[$index][$subindex][$group][$key] = $value;
    }
}

foreach ($chan_stats as $index => $entry) {
    list($index,$subindex) = explode('.', $index, 2);
    $group                 = 'c6kxbar';
    foreach ($entry as $key => $value) {
        $entPhysical_state[$index][$subindex][$group][$key] = $value;
    }

    $rrd_name = array('c6kxbar', $index, $subindex);
    $rrd_def = array(
        'DS:inutil:GAUGE:600:0:100',
        'DS:oututil:GAUGE:600:0:100',
        'DS:outdropped:DERIVE:600:0:125000000000',
        'DS:outerrors:DERIVE:600:0:125000000000',
        'DS:inerrors:DERIVE:600:0:125000000000'
    );

    $fields = array(
        'inutil'      => $entry['cc6kxbarStatisticsInUtil'],
        'oututil'     => $entry['cc6kxbarStatisticsOutUtil'],
        'outdropped'  => $entry['cc6kxbarStatisticsOutDropped'],
        'outerrors'   => $entry['cc6kxbarStatisticsOutErrors'],
        'inerrors'    => $entry['cc6kxbarStatisticsInErrors'],
    );

    $tags = compact('index', 'subindex', 'rrd_name', 'rrd_def');
    data_update($device, 'c6kxbar', $tags, $fields);
}//end foreach
