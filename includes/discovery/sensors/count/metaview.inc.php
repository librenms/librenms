<?php

/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link       https://www.librenms.org
*/

$count_oids = [
    '.1.2.826.0.1.1578918.6.1579.1.19' => [
        'descr' => 'Calls currently connected',
        'index' => 'cFSStatsR4TIClsClsCurCon',
        'group' => 'Traffic Information: Calls',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.20' => [
        'descr' => 'Calls currently connecting',
        'index' => 'cFSStatsR4TIClsCallsCurrConnecting',
        'group' => 'Traffic Information: Calls',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.21' => [
        'descr' => 'Calls currently disconnecting',
        'index' => 'cFSStatsR4TIClsCallsCurrDisconnecting',
        'group' => 'Traffic Information: Calls',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.23' => [
        'descr' => 'Emergency calls currently connected',
        'index' => 'cFSStatsR4TrfcIEmgncyCallsClsCurCon',
        'group' => 'Traffic Information: Emergency Calls',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.24' => [
        'descr' => 'Emergency calls currently connecting',
        'index' => 'cFSStatsR4TrfcIEmgncyCallsCallsCurrConnecting',
        'group' => 'Traffic Information: Emergency Calls',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.26' => [
        'descr' => 'Total call attempts',
        'index' => 'cFSStatsR4CAtsSubscr',
        'group' => 'Traffic Information: Calls originated on-switch',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.27' => [
        'descr' => 'Total call attempts',
        'index' => 'cFSStatsR4CAtsTrnk',
        'group' => 'Traffic Information: Calls from trunks',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.29' => [
        'descr' => 'Failed call attempt',
        'index' => 'cFSStatsR4CFlsTotSubscr',
        'group' => 'Traffic Information: Calls originated on-switch',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.30' => [
        'descr' => 'Failed call attempts',
        'index' => 'cFSStatsR4CFlsTotTrnk',
        'group' => 'Traffic Information: Calls from trunks',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.32' => [
        'descr' => 'Calls originated on-switch subscriber busy',
        'index' => 'cFSStatsR4CFlsSubBusySubscr',
        'group' => 'Call attempt failures: Called on-switch subscriber busy',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.33' => [
        'descr' => 'Calls from trunks subscribers busy',
        'index' => 'cFSStatsR4CFlsSubBusyTrnk',
        'group' => 'Call attempt failures: Called on-switch subscriber busy',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.35' => [
        'descr' => 'Calls originated on-switch subscriber device unavailable',
        'index' => 'cFSStatsR4CFlsSubUnavailSubscr',
        'group' => 'Call attempt failures: Called on-switch subscriber device unavailable',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.36' => [
        'descr' => 'Calls from trunks subscriber device unavailable',
        'index' => 'cFSStatsR4CFlsSubUnavailTrnk',
        'group' => 'Call attempt failures: Called on-switch subscriber device unavailable',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.41' => [
        'descr' => 'On-switch remote called subscriber or remote trunk busy',
        'index' => 'cFSStatsR4CFlsTrnkNetBOrFSubscr',
        'group' => 'Call attempt failures: Remote called subscriber or remote trunk busy',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.42' => [
        'descr' => 'Trunks remote called subscriber or remote trunk busy',
        'index' => 'cFSStatsR4CFlsTrnkNetBOrFTrnk',
        'group' => 'Call attempt failures: Remote called subscriber or remote trunk busy',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.53' => [
        'descr' => 'Calls originated on-switch routing failed',
        'index' => 'cFSStatsR4CFlsRtgFaildSubscr',
        'group' => 'Call attempt failures: Routing failed',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.54' => [
        'descr' => 'Calls from trunks routing failed',
        'index' => 'cFSStatsR4CFlsRtgFaildTrnk',
        'group' => 'Call attempt failures: Routing failed',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.62' => [
        'descr' => 'Calls originated on-switch signaling failed',
        'index' => 'cFSStatsR4CFlsSigFaildSubscr',
        'group' => 'Call attempt failures: Signaling failed',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.63' => [
        'descr' => 'Calls from trunks signaling failed',
        'index' => 'cFSStatsR4CFlsSigFaildTrnk',
        'group' => 'Call attempt failures: Signaling failed',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.65' => [
        'descr' => 'Calls originated on-switch system congestion',
        'index' => 'cFSStatsR4CFlsSysCgstnSubscr',
        'group' => 'Call attempt failures: System congestion',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.66' => [
        'descr' => 'Calls from trunks system congestion',
        'index' => 'cFSStatsR4CFlsSysCgstnTrnk',
        'group' => 'Call attempt failures: System congestion',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.71' => [
        'descr' => 'Calls originated on-switch',
        'index' => 'cFSStatsR4ActCFlsSubscr',
        'group' => 'Traffic Information: Connected call failures',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.72' => [
        'descr' => 'Calls from trunks',
        'index' => 'cFSStatsR4ActCFlsTrnk',
        'group' => 'Traffic Information: Connected call failures',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.73' => [
        'descr' => 'Calls originated on-switch calls service failures',
        'index' => 'cFSStatsR4CFlsCallServsSubscr',
        'group' => 'Calls attempt failures: Handled by Call Service',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.74' => [
        'descr' => 'Calls from trunks calls service failures',
        'index' => 'cFSStatsR4CFlsCallServsTrnk',
        'group' => 'Calls attempt failures: Handled by Call Service',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.75' => [
        'descr' => 'Calls in progress',
        'index' => 'cFSStatsR4SubOrigCallsNum',
        'group' => 'Traffic Information: Calls originated on-switch',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.76' => [
        'descr' => 'Calls in progress (high)',
        'index' => 'cFSStatsR4SubOrigCallsHighWaterMark',
        'group' => 'Traffic Information: Calls originated on-switch',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.77' => [
        'descr' => 'Calls in progress (low)',
        'index' => 'cFSStatsR4SubOrigCallsLowWaterMark',
        'group' => 'Traffic Information: Calls originated on-switch',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.78' => [
        'descr' => 'Call usage',
        'index' => 'cFSStatsR4TIClsOriginatedOnSwitchCallUsage',
        'group' => 'Traffic Information: Calls originated on-switch',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.79' => [
        'descr' => 'Calls in progress',
        'index' => 'cFSStatsR4InTrnkCallsNum',
        'group' => 'Traffic Information: Calls from trunks',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.80' => [
        'descr' => 'Calls in progress (high)',
        'index' => 'cFSStatsR4InTrnkCallsHighWaterMark',
        'group' => 'Traffic Information: Calls from trunks',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.81' => [
        'descr' => 'Calls in progress (low)',
        'index' => 'cFSStatsR4InTrnkCallsLowWaterMark',
        'group' => 'Traffic Information: Calls from trunks',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.82' => [
        'descr' => 'Call usage',
        'index' => 'cFSStatsR4TrfcITrnksCallUsage',
        'group' => 'Traffic Information: Calls from trunks',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.83' => [
        'descr' => 'On-switch successful call attempts',
        'index' => 'cFSStatsR4CTpOnSwSuccessfulCallAttempts',
        'group' => 'Call topology: On-switch',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.84' => [
        'descr' => 'On-switch Calls currently connected',
        'index' => 'cFSStatsR4CTpOnSwClsCCon',
        'group' => 'Call topology: On-switch',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.85' => [
        'descr' => 'On-switch Calls currently connected (high)',
        'index' => 'cFSStatsR4CTpOnSwClsCConHighWaterMark',
        'group' => 'Call topology: On-switch',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.86' => [
        'descr' => 'On-switch Calls currently connected (low)',
        'index' => 'cFSStatsR4CTpOnSwClsCConLowWaterMark',
        'group' => 'Call topology: On-switch',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.87' => [
        'descr' => 'On-switch Call usage',
        'index' => 'cFSStatsR4CTpOnSwCallUsage',
        'group' => 'Call topology: On-switch',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.88' => [
        'descr' => 'On-switch subscriber to trunk - Successful call attempts',
        'index' => 'cFSStatsR4CTpOnSwSubToTrnkSuccessfulCallAttempts',
        'group' => 'Call topology: On-switch Subscriber to Trunk',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.89' => [
        'descr' => 'On-switch subscriber to trunk - Calls currently connected',
        'index' => 'cFSStatsR4CTOnSwSbTrClsCCon',
        'group' => 'Call topology: On-switch Subscriber to Trunk',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.90' => [
        'descr' => 'On-switch subscriber to trunk - Calls currently connected (high)',
        'index' => 'cFSStatsR4CTOnSwSbTrClsCConHighWaterMark',
        'group' => 'Call topology: On-switch Subscriber to Trunk',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.91' => [
        'descr' => 'On-switch subscriber to trunk - Calls currently connected (low)',
        'index' => 'cFSStatsR4CTOnSwSbTrClsCConLowWaterMark',
        'group' => 'Call topology: On-switch Subscriber to Trunk',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.92' => [
        'descr' => 'On-switch subscriber to trunk - Call usage',
        'index' => 'cFSStatsR4CTpOnSwSubToTrnkCallUsage',
        'group' => 'Call topology: On-switch Subscriber to Trunk',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.93' => [
        'descr' => 'On-switch subscriber to IVR - Successful call attempts',
        'index' => 'cFSStatsR4CTpOnSwSubToIVRSuccessfulCallAttempts',
        'group' => 'Call topology: On-switch Subscriber to IVR',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.94' => [
        'descr' => 'On-switch subscriber to IVR - Calls currently connected',
        'index' => 'cFSStatsR4CTpSwSubToIVRCCon',
        'group' => 'Call topology: On-switch Subscriber to IVR',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.95' => [
        'descr' => 'On-switch subscriber to IVR - Calls currently connected (high)',
        'index' => 'cFSStatsR4CTpSwSubToIVRCConHighWaterMark',
        'group' => 'Call topology: On-switch Subscriber to IVR',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.96' => [
        'descr' => 'On-switch subscriber to IVR - Calls currently connected (low)',
        'index' => 'cFSStatsR4CTpSwSubToIVRCConLowWaterMark',
        'group' => 'Call topology: On-switch Subscriber to IVR',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.97' => [
        'descr' => 'On-switch subscriber to IVR - Call usage',
        'index' => 'cFSStatsR4CTpSwSubToIVRCCallUsage',
        'group' => 'Call topology: On-switch Subscriber to IVR',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.98' => [
        'descr' => 'Trunk to on-switch subscriber - Successful call attempts',
        'index' => 'cFSStatsR4CTpTrOnSwSbSuccessfulCallAttempts',
        'group' => 'Call topology: Trunk to On-switch Subscriber',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.99' => [
        'descr' => 'Trunk to on-switch subscriber - Calls currently connected',
        'index' => 'cFSStatsR4CTpTrOnSwSbClsCCon',
        'group' => 'Call topology: Trunk to On-switch Subscriber',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.100' => [
        'descr' => 'Trunk to on-switch subscriber - Calls currently connected (high)',
        'index' => 'cFSStatsR4CTpTrOnSwSbClsCConHighWaterMark',
        'group' => 'Call topology: Trunk to On-switch Subscriber',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.101' => [
        'descr' => 'Trunk to on-switch subscriber - Calls currently connected (low)',
        'index' => 'cFSStatsR4CTpTrOnSwSbClsCConLowWaterMark',
        'group' => 'Call topology: Trunk to On-switch Subscriber',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.102' => [
        'descr' => 'Trunk to on-switch - Call usage',
        'index' => 'cFSStatsR4CTpTrSubToOnSwSuccessfulCallAttempts',
        'group' => 'Call topology: Trunk to On-switch Subscriber',
    ],
    '.1.2.826.0.1.1578918.6.1579.1.118' => [
        'descr' => 'Calls reaching max duration',
        'index' => 'cFSStatsR4TrfcIConnectedCallFailsCallsReachingMaxDurn',
        'group' => 'Traffic Information: Connected call failures',
    ],
];

$allNumOids = array_keys($count_oids);
$snmp_data = SnmpQuery::numeric()->next($allNumOids)->values();
$idx = 0;

foreach ($snmp_data as $sensor_oid => $sensor_current) {
    $num_oid = $allNumOids[$idx++] ?? null;

    if ($num_oid === null || ! isset($count_oids[$num_oid])) {
        break;
    }

    $oid_info = $count_oids[$num_oid];

    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'count',
        'sensor_oid' => $sensor_oid,
        'sensor_index' => $oid_info['index'],
        'sensor_type' => 'metaview',
        'sensor_descr' => $oid_info['descr'],
        'sensor_divisor' => 1,
        'sensor_multiplier' => 1,
        'sensor_current' => $sensor_current,
        'group' => $oid_info['group'] ?? null,
    ]));
}
