<?php

// IP-MIB::ipSystemStatsInReceives.ipv4 = Counter32: 1062322382
// IP-MIB::ipSystemStatsInReceives.ipv6 = Counter32: 5229983
// IP-MIB::ipSystemStatsHCInReceives.ipv4 = Counter64: 1062322382
// IP-MIB::ipSystemStatsHCInReceives.ipv6 = Counter64: 5229983
// IP-MIB::ipSystemStatsHCInOctets.ipv4 = Counter64: 0
// IP-MIB::ipSystemStatsHCInOctets.ipv6 = Counter64: 0
// IP-MIB::ipSystemStatsInHdrErrors.ipv4 = Counter32: 199
// IP-MIB::ipSystemStatsInHdrErrors.ipv6 = Counter32: 0
// IP-MIB::ipSystemStatsInAddrErrors.ipv4 = Counter32: 0
// IP-MIB::ipSystemStatsInAddrErrors.ipv6 = Counter32: 0
// IP-MIB::ipSystemStatsInUnknownProtos.ipv4 = Counter32: 1
// IP-MIB::ipSystemStatsInUnknownProtos.ipv6 = Counter32: 0
// IP-MIB::ipSystemStatsInForwDatagrams.ipv4 = Counter32: 4350883
// IP-MIB::ipSystemStatsInForwDatagrams.ipv6 = Counter32: 0
// IP-MIB::ipSystemStatsHCInForwDatagrams.ipv4 = Counter64: 4350883
// IP-MIB::ipSystemStatsHCInForwDatagrams.ipv6 = Counter64: 0
// IP-MIB::ipSystemStatsReasmReqds.ipv4 = Counter32: 0
// IP-MIB::ipSystemStatsReasmReqds.ipv6 = Counter32: 0
// IP-MIB::ipSystemStatsReasmOKs.ipv4 = Counter32: 573
// IP-MIB::ipSystemStatsReasmOKs.ipv6 = Counter32: 191
// IP-MIB::ipSystemStatsReasmFails.ipv4 = Counter32: 2
// IP-MIB::ipSystemStatsReasmFails.ipv6 = Counter32: 0
// IP-MIB::ipSystemStatsInDiscards.ipv4 = Counter32: 0
// IP-MIB::ipSystemStatsInDiscards.ipv6 = Counter32: 0
// IP-MIB::ipSystemStatsInDelivers.ipv4 = Counter32: 1053500708
// IP-MIB::ipSystemStatsInDelivers.ipv6 = Counter32: 5229756
// IP-MIB::ipSystemStatsHCInDelivers.ipv4 = Counter64: 1053500708
// IP-MIB::ipSystemStatsHCInDelivers.ipv6 = Counter64: 5229756
// IP-MIB::ipSystemStatsOutRequests.ipv4 = Counter32: 874021272
// IP-MIB::ipSystemStatsOutRequests.ipv6 = Counter32: 5157066
// IP-MIB::ipSystemStatsHCOutRequests.ipv4 = Counter64: 874021272
// IP-MIB::ipSystemStatsHCOutRequests.ipv6 = Counter64: 5157066
// IP-MIB::ipSystemStatsOutNoRoutes.ipv4 = Counter32: 1
// IP-MIB::ipSystemStatsOutNoRoutes.ipv6 = Counter32: 0
// IP-MIB::ipSystemStatsHCOutForwDatagrams.ipv4 = Counter64: 0
// IP-MIB::ipSystemStatsHCOutForwDatagrams.ipv6 = Counter64: 0
// IP-MIB::ipSystemStatsOutDiscards.ipv4 = Counter32: 205
// IP-MIB::ipSystemStatsOutDiscards.ipv6 = Counter32: 0
// IP-MIB::ipSystemStatsOutFragFails.ipv4 = Counter32: 0
// IP-MIB::ipSystemStatsOutFragFails.ipv6 = Counter32: 0
// IP-MIB::ipSystemStatsOutFragCreates.ipv4 = Counter32: 0
// IP-MIB::ipSystemStatsOutFragCreates.ipv6 = Counter32: 68
// IP-MIB::ipSystemStatsDiscontinuityTime.ipv4 = Timeticks: (0) 0:00:00.00
// IP-MIB::ipSystemStatsDiscontinuityTime.ipv6 = Timeticks: (0) 0:00:00.00
// IP-MIB::ipSystemStatsRefreshRate.ipv4 = Gauge32: 30000 milli-seconds
// IP-MIB::ipSystemStatsRefreshRate.ipv6 = Gauge32: 30000 milli-seconds

use LibreNMS\RRD\RrdDefinition;

$data = snmpwalk_cache_oid($device, 'ipSystemStats', null, 'IP-MIB');

if ($data) {
    $oids = [
        'ipSystemStatsInReceives',
        'ipSystemStatsInHdrErrors',
        'ipSystemStatsInAddrErrors',
        'ipSystemStatsInUnknownProtos',
        'ipSystemStatsInForwDatagrams',
        'ipSystemStatsReasmReqds',
        'ipSystemStatsReasmOKs',
        'ipSystemStatsReasmFails',
        'ipSystemStatsInDiscards',
        'ipSystemStatsInDelivers',
        'ipSystemStatsOutRequests',
        'ipSystemStatsOutNoRoutes',
        'ipSystemStatsOutDiscards',
        'ipSystemStatsOutFragFails',
        'ipSystemStatsOutFragCreates',
        'ipSystemStatsOutForwDatagrams',
    ];

    foreach ($data as $af => $stats) {
        echo "$af ";

        // Use HC counters instead if they're available.
        if (isset($stats['ipSystemStatsHCInReceives'])) {
            $stats['ipSystemStatsInReceives'] = $stats['ipSystemStatsHCInReceives'];
        }

        if (isset($stats['ipSystemStatsHCInForwDatagrams'])) {
            $stats['ipSystemStatsInForwDatagrams'] = $stats['ipSystemStatsHCInForwDatagrams'];
        }

        if (isset($stats['ipSystemStatsHCInDelivers'])) {
            $stats['ipSystemStatsInDelivers'] = $stats['ipSystemStatsHCInDelivers'];
        }

        if (isset($stats['ipSystemStatsHCOutRequests'])) {
            $stats['ipSystemStatsOutRequests'] = $stats['ipSystemStatsHCOutRequests'];
        }

        if (isset($stats['ipSystemStatsHCOutForwDatagrams'])) {
            $stats['ipSystemStatsOutForwDatagrams'] = $stats['ipSystemStatsHCOutForwDatagrams'];
        }

        $rrd_name = ['ipSystemStats', $af];
        $rrd_def = new RrdDefinition();
        $fields = [];

        foreach ($oids as $oid) {
            $oid_ds = str_replace('ipSystemStats', '', $oid);
            $rrd_def->addDataset($oid_ds, 'COUNTER');
            if (strstr($stats[$oid], 'No') || strstr($stats[$oid], 'd') || strstr($stats[$oid], 's')) {
                $stats[$oid] = '0';
            }
            $fields[$oid_ds] = $stats[$oid];
        }

        $tags = compact('af', 'rrd_name', 'rrd_def');
        data_update($device, 'ipSystemStats', $tags, $fields);

        // FIXME per-AF?
        $os->enableGraph("ipsystemstats_$af");
        $os->enableGraph("ipsystemstats_{$af}_frag");
    }//end foreach
}//end if

unset($oids, $data);
echo "\n";
