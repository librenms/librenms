<?php

use LibreNMS\RRD\RrdDefinition;

echo "Cisco Cat6xxx/76xx Crossbar : \n";

$mod_stats = snmpwalk_cache_oid($device, 'cc6kxbarModuleModeTable', [], 'CISCO-CAT6K-CROSSBAR-MIB');

foreach ($mod_stats as $index => $entry) {
    $group = 'c6kxbar';
    foreach ($entry as $key => $value) {
        $subindex = null;
        $entPhysical_state[$index][$subindex][$group][$key] = $value;
    }
}

$chan_stats = snmpwalk_cache_oid($device, 'cc6kxbarModuleChannelTable', [], 'CISCO-CAT6K-CROSSBAR-MIB');
if (! empty($chan_stats)) {
    $chan_stats = snmpwalk_cache_oid($device, 'cc6kxbarStatisticsTable', $chan_stats, 'CISCO-CAT6K-CROSSBAR-MIB');
}

foreach ($chan_stats as $index => $entry) {
    [$index,$subindex] = explode('.', $index, 2);
    $group = 'c6kxbar';
    foreach ($entry as $key => $value) {
        $entPhysical_state[$index][$subindex][$group][$key] = $value;
    }

    $rrd_name = ['c6kxbar', $index, $subindex];
    $rrd_def = RrdDefinition::make()
        ->addDataset('inutil', 'GAUGE', 0, 100)
        ->addDataset('oututil', 'GAUGE', 0, 100)
        ->addDataset('outdropped', 'DERIVE', 0, 125000000000)
        ->addDataset('outerrors', 'DERIVE', 0, 125000000000)
        ->addDataset('inerrors', 'DERIVE', 0, 125000000000);

    $fields = [
        'inutil'      => $entry['cc6kxbarStatisticsInUtil'],
        'oututil'     => $entry['cc6kxbarStatisticsOutUtil'],
        'outdropped'  => $entry['cc6kxbarStatisticsOutDropped'],
        'outerrors'   => $entry['cc6kxbarStatisticsOutErrors'],
        'inerrors'    => $entry['cc6kxbarStatisticsInErrors'],
    ];

    $tags = compact('index', 'subindex', 'rrd_name', 'rrd_def');
    data_update($device, 'c6kxbar', $tags, $fields);
}//end foreach
