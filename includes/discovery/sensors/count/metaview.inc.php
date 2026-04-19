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
    'cFSStatsR4TIClsClsCurCon' => [
        'descr' => 'Calls currently connected',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.19',
        'group' => 'Traffic Information: Calls',
    ],
    'cFSStatsR4TIClsCallsCurrConnecting' => [
        'descr' => 'Calls currently connecting',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.20',
        'group' => 'Traffic Information: Calls',
    ],
    'cFSStatsR4TIClsCallsCurrDisconnecting' => [
        'descr' => 'Calls currently disconnecting',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.21',
        'group' => 'Traffic Information: Calls',
    ],
    'cFSStatsR4TrfcIEmgncyCallsClsCurCon' => [
        'descr' => 'Emergency calls currently connected',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.23',
        'group' => 'Traffic Information: Emergency Calls',
    ],
    'cFSStatsR4TrfcIEmgncyCallsCallsCurrConnecting' => [
        'descr' => 'Emergency calls currently connecting',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.24',
        'group' => 'Traffic Information: Emergency Calls',
    ],
    'cFSStatsR4CAtsSubscr' => [
        'descr' => 'Total call attempts',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.26',
        'group' => 'Traffic Information: Calls originated on-switch',
    ],
    'cFSStatsR4CAtsTrnk' => [
        'descr' => 'Total call attempts',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.27',
        'group' => 'Traffic Information: Calls from trunks',
    ],
    'cFSStatsR4CFlsTotSubscr' => [
        'descr' => 'Failed call attempt',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.29',
        'group' => 'Traffic Information: Calls originated on-switch',
    ],
    'cFSStatsR4CFlsTotTrnk' => [
        'descr' => 'Failed call attempts',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.30',
        'group' => 'Traffic Information: Calls from trunks',
    ],
    'cFSStatsR4CFlsSubBusySubscr' => [
        'descr' => 'Calls originated on-switch subscriber busy',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.32',
        'group' => 'Call attempt failures: Called on-switch subscriber busy',
    ],
    'cFSStatsR4CFlsSubBusyTrnk' => [
        'descr' => 'Calls from trunks subscribers busy',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.33',
        'group' => 'Call attempt failures: Called on-switch subscriber busy',
    ],
    'cFSStatsR4CFlsSubUnavailSubscr' => [
        'descr' => 'Calls originated on-switch subscriber device unavailable',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.35',
        'group' => 'Call attempt failures: Called on-switch subscriber device unavailable',
    ],
    'cFSStatsR4CFlsSubUnavailTrnk' => [
        'descr' => 'Calls from trunks subscriber device unavailable',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.36',
        'group' => 'Call attempt failures: Called on-switch subscriber device unavailable',
    ],
    'cFSStatsR4CFlsTrnkNetBOrFSubscr' => [
        'descr' => 'On-switch remote called subscriber or remote trunk busy',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.41',
        'group' => 'Call attempt failures: Remote called subscriber or remote trunk busy',
    ],
    'cFSStatsR4CFlsTrnkNetBOrFTrnk' => [
        'descr' => 'Trunks remote called subscriber or remote trunk busy',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.42',
        'group' => 'Call attempt failures: Remote called subscriber or remote trunk busy',
    ],
    'cFSStatsR4CFlsRtgFaildSubscr' => [
        'descr' => 'Calls originated on-switch routing failed',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.53',
        'group' => 'Call attempt failures: Routing failed',
    ],
    'cFSStatsR4CFlsRtgFaildTrnk' => [
        'descr' => 'Calls from trunks routing failed',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.54',
        'group' => 'Call attempt failures: Routing failed',
    ],
    'cFSStatsR4CFlsSigFaildSubscr' => [
        'descr' => 'Calls originated on-switch signaling failed',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.62',
        'group' => 'Call attempt failures: Signaling failed',
    ],
    'cFSStatsR4CFlsSigFaildTrnk' => [
        'descr' => 'Calls from trunks signaling failed',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.63',
        'group' => 'Call attempt failures: Signaling failed',
    ],
    'cFSStatsR4CFlsSysCgstnSubscr' => [
        'descr' => 'Calls originated on-switch system congestion',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.65',
        'group' => 'Call attempt failures: System congestion',
    ],
    'cFSStatsR4CFlsSysCgstnTrnk' => [
        'descr' => 'Calls from trunks system congestion',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.66',
        'group' => 'Call attempt failures: System congestion',
    ],
    'cFSStatsR4ActCFlsSubscr' => [
        'descr' => 'Calls originated on-switch',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.71',
        'group' => 'Traffic Information: Connected call failures',
    ],
    'cFSStatsR4ActCFlsTrnk' => [
        'descr' => 'Calls from trunks',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.72',
        'group' => 'Traffic Information: Connected call failures',
    ],
    'cFSStatsR4CFlsCallServsSubscr' => [
        'descr' => 'Calls originated on-switch calls service failures',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.73',
        'group' => 'Calls attempt failures: Handled by Call Service',
    ],
    'cFSStatsR4CFlsCallServsTrnk' => [
        'descr' => 'Calls from trunks calls service failures',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.74',
        'group' => 'Calls attempt failures: Handled by Call Service',
    ],
    'cFSStatsR4SubOrigCallsNum' => [
        'descr' => 'Calls in progress',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.75',
        'group' => 'Traffic Information: Calls originated on-switch',
    ],
    'cFSStatsR4SubOrigCallsHighWaterMark' => [
        'descr' => 'Calls in progress (high)',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.76',
        'group' => 'Traffic Information: Calls originated on-switch',
    ],
    'cFSStatsR4SubOrigCallsLowWaterMark' => [
        'descr' => 'Calls in progress (low)',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.77',
        'group' => 'Traffic Information: Calls originated on-switch',
    ],
    'cFSStatsR4TIClsOriginatedOnSwitchCallUsage' => [
        'descr' => 'Call usage',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.78',
        'group' => 'Traffic Information: Calls originated on-switch',
    ],
    'cFSStatsR4InTrnkCallsNum' => [
        'descr' => 'Calls in progress',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.79',
        'group' => 'Traffic Information: Calls from trunks',
    ],
    'cFSStatsR4InTrnkCallsHighWaterMark' => [
        'descr' => 'Calls in progress (high)',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.80',
        'group' => 'Traffic Information: Calls from trunks',
    ],
    'cFSStatsR4InTrnkCallsLowWaterMark' => [
        'descr' => 'Calls in progress (low)',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.81',
        'group' => 'Traffic Information: Calls from trunks',
    ],
    'cFSStatsR4TrfcITrnksCallUsage' => [
        'descr' => 'Call usage',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.82',
        'group' => 'Traffic Information: Calls from trunks',
    ],
    'cFSStatsR4CTpOnSwSuccessfulCallAttempts' => [
        'descr' => 'On-switch successful call attempts',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.83',
        'group' => 'Call topology: On-switch',
    ],
    'cFSStatsR4CTpOnSwClsCCon' => [
        'descr' => 'On-switch Calls currently connected',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.84',
        'group' => 'Call topology: On-switch',
    ],
    'cFSStatsR4CTpOnSwClsCConHighWaterMark' => [
        'descr' => 'On-switch Calls currently connected (high)',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.85',
        'group' => 'Call topology: On-switch',
    ],
    'cFSStatsR4CTpOnSwClsCConLowWaterMark' => [
        'descr' => 'On-switch Calls currently connected (low)',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.86',
        'group' => 'Call topology: On-switch',
    ],
    'cFSStatsR4CTpOnSwCallUsage' => [
        'descr' => 'On-switch Call usage',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.87',
        'group' => 'Call topology: On-switch',
    ],
    'cFSStatsR4CTpOnSwSubToTrnkSuccessfulCallAttempts' => [
        'descr' => 'On-switch subscriber to trunk - Successful call attempts',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.88',
        'group' => 'Call topology: On-switch Subscriber to Trunk',
    ],
    'cFSStatsR4CTOnSwSbTrClsCCon' => [
        'descr' => 'On-switch subscriber to trunk - Calls currently connected',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.89',
        'group' => 'Call topology: On-switch Subscriber to Trunk',
    ],
    'cFSStatsR4CTOnSwSbTrClsCConHighWaterMark' => [
        'descr' => 'On-switch subscriber to trunk - Calls currently connected (high)',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.90',
        'group' => 'Call topology: On-switch Subscriber to Trunk',
    ],
    'cFSStatsR4CTOnSwSbTrClsCConLowWaterMark' => [
        'descr' => 'On-switch subscriber to trunk - Calls currently connected (low)',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.91',
        'group' => 'Call topology: On-switch Subscriber to Trunk',
    ],
    'cFSStatsR4CTpOnSwSubToTrnkCallUsage' => [
        'descr' => 'On-switch subscriber to trunk - Call usage',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.92',
        'group' => 'Call topology: On-switch Subscriber to Trunk',
    ],
    'cFSStatsR4CTpOnSwSubToIVRSuccessfulCallAttempts' => [
        'descr' => 'On-switch subscriber to IVR - Successful call attempts',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.93',
        'group' => 'Call topology: On-switch Subscriber to IVR',
    ],
    'cFSStatsR4CTpSwSubToIVRCCon' => [
        'descr' => 'On-switch subscriber to IVR - Calls currently connected',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.94',
        'group' => 'Call topology: On-switch Subscriber to IVR',
    ],
    'cFSStatsR4CTpSwSubToIVRCConHighWaterMark' => [
        'descr' => 'On-switch subscriber to IVR - Calls currently connected (high)',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.95',
        'group' => 'Call topology: On-switch Subscriber to IVR',
    ],
    'cFSStatsR4CTpSwSubToIVRCConLowWaterMark' => [
        'descr' => 'On-switch subscriber to IVR - Calls currently connected (low)',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.96',
        'group' => 'Call topology: On-switch Subscriber to IVR',
    ],
    'cFSStatsR4CTpSwSubToIVRCCallUsage' => [
        'descr' => 'On-switch subscriber to IVR - Call usage',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.97',
        'group' => 'Call topology: On-switch Subscriber to IVR',
    ],
    'cFSStatsR4CTpTrOnSwSbSuccessfulCallAttempts' => [
        'descr' => 'Trunk to on-switch subscriber - Successful call attempts',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.98',
        'group' => 'Call topology: Trunk to On-switch Subscriber',
    ],
    'cFSStatsR4CTpTrOnSwSbClsCCon' => [
        'descr' => 'Trunk to on-switch subscriber - Calls currently connected',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.99',
        'group' => 'Call topology: Trunk to On-switch Subscriber',
    ],
    'cFSStatsR4CTpTrOnSwSbClsCConHighWaterMark' => [
        'descr' => 'Trunk to on-switch subscriber - Calls currently connected (high)',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.100',
        'group' => 'Call topology: Trunk to On-switch Subscriber',
    ],
    'cFSStatsR4CTpTrOnSwSbClsCConLowWaterMark' => [
        'descr' => 'Trunk to on-switch subscriber - Calls currently connected (low)',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.101',
        'group' => 'Call topology: Trunk to On-switch Subscriber',
    ],
    'cFSStatsR4CTpTrSubToOnSwSuccessfulCallAttempts' => [
        'descr' => 'Trunk to on-switch - Call usage',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.102',
        'group' => 'Call topology: Trunk to On-switch Subscriber',
    ],
    'cFSStatsR4TrfcIConnectedCallFailsCallsReachingMaxDurn' => [
        'descr' => 'Calls reaching max duration',
        'num_oid' => '.1.2.826.0.1.1578918.6.1579.1.118',
        'group' => 'Traffic Information: Connected call failures',
    ],
];

foreach ($count_oids as $name => $oid_info) {
    $data = SnmpQuery::numeric()->next($oid_info['num_oid'])->values();
    $oid = key($data);
    $value = $data[$oid];

    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'count',
        'sensor_oid' => $oid,
        'sensor_index' => $name,
        'sensor_type' => 'metaview',
        'sensor_descr' => $oid_info['descr'],
        'sensor_divisor' => 1,
        'sensor_multiplier' => 1,
        'sensor_current' => $value,
        'group' => $oid_info['group'] ?? null,
    ]));
}
