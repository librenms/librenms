<?php

use LibreNMS\RRD\RrdDefinition;

// Poll systemStats from UNIX-like hosts running UCD/Net-SNMPd
// UCD-SNMP-MIB::ssIndex.0 = INTEGER: 1
// UCD-SNMP-MIB::ssErrorName.0 = STRING: systemStats
// UCD-SNMP-MIB::ssSwapIn.0 = INTEGER: 0 kB
// UCD-SNMP-MIB::ssSwapOut.0 = INTEGER: 0 kB
// UCD-SNMP-MIB::ssIOSent.0 = INTEGER: 1864 blocks/s
// UCD-SNMP-MIB::ssIOReceive.0 = INTEGER: 7 blocks/s
// UCD-SNMP-MIB::ssSysInterrupts.0 = INTEGER: 7572 interrupts/s
// UCD-SNMP-MIB::ssSysContext.0 = INTEGER: 10254 switches/s
// UCD-SNMP-MIB::ssCpuUser.0 = INTEGER: 4
// UCD-SNMP-MIB::ssCpuSystem.0 = INTEGER: 3
// UCD-SNMP-MIB::ssCpuIdle.0 = INTEGER: 77
// UCD-SNMP-MIB::ssCpuRawUser.0 = Counter32: 194386556
// UCD-SNMP-MIB::ssCpuRawNice.0 = Counter32: 15673
// UCD-SNMP-MIB::ssCpuRawSystem.0 = Counter32: 65382910
// UCD-SNMP-MIB::ssCpuRawIdle.0 = Counter32: 1655192684
// UCD-SNMP-MIB::ssCpuRawWait.0 = Counter32: 205336019
// UCD-SNMP-MIB::ssCpuRawKernel.0 = Counter32: 0
// UCD-SNMP-MIB::ssCpuRawInterrupt.0 = Counter32: 1128048
// UCD-SNMP-MIB::ssIORawSent.0 = Counter32: 2353983704
// UCD-SNMP-MIB::ssIORawReceived.0 = Counter32: 3172182750
// UCD-SNMP-MIB::ssRawInterrupts.0 = Counter32: 427446276
// UCD-SNMP-MIB::ssRawContexts.0 = Counter32: 4161026807
// UCD-SNMP-MIB::ssCpuRawSoftIRQ.0 = Counter32: 2605010
// UCD-SNMP-MIB::ssRawSwapIn.0 = Counter32: 602002
// UCD-SNMP-MIB::ssRawSwapOut.0 = Counter32: 937422
// UCD-SNMP-MIB::ssCpuRawWait.0
// UCD-SNMP-MIB::ssCpuRawSteal.0

$ss = snmpwalk_cache_oid($device, 'systemStats', [], 'UCD-SNMP-MIB');
$ss = $ss[0];

if (is_numeric($ss['ssCpuRawUser']) && is_numeric($ss['ssCpuRawNice']) && is_numeric($ss['ssCpuRawSystem']) && is_numeric($ss['ssCpuRawIdle'])) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('user', 'COUNTER', 0)
        ->addDataset('system', 'COUNTER', 0)
        ->addDataset('nice', 'COUNTER', 0)
        ->addDataset('idle', 'COUNTER', 0);

    $fields = [
        'user'    => $ss['ssCpuRawUser'],
        'system'  => $ss['ssCpuRawSystem'],
        'nice'    => $ss['ssCpuRawNice'],
        'idle'    => $ss['ssCpuRawIdle'],
    ];

    $tags = compact('rrd_def');
    data_update($device, 'ucd_cpu', $tags, $fields);

    $os->enableGraph('ucd_cpu');
}

// This is how we'll collect in the future, start now so people don't have zero data.
$collect_oids = [
    'ssCpuRawUser',
    'ssCpuRawNice',
    'ssCpuRawSystem',
    'ssCpuRawIdle',
    'ssCpuRawInterrupt',
    'ssCpuRawSoftIRQ',
    'ssCpuRawKernel',
    'ssCpuRawWait',
    'ssIORawSent',
    'ssIORawReceived',
    'ssRawInterrupts',
    'ssRawContexts',
    'ssRawSwapIn',
    'ssRawSwapOut',
    'ssCpuRawWait',
    'ssCpuRawSteal',
];

foreach ($collect_oids as $oid) {
    if (is_numeric($ss[$oid])) {
        $rrd_name = 'ucd_' . $oid;
        $rrd_def = RrdDefinition::make()->addDataset('value', 'COUNTER', 0);

        $fields = [
            'value' => $ss[$oid],
        ];

        $tags = compact('oid', 'rrd_name', 'rrd_def');
        data_update($device, 'ucd_cpu', $tags, $fields);

        $os->enableGraph('ucd_cpu');
    }
}

// Set various graphs if we've seen the right OIDs.
if (is_numeric($ss['ssRawSwapIn'])) {
    $os->enableGraph('ucd_swap_io');
}

if (is_numeric($ss['ssIORawSent'])) {
    $os->enableGraph('ucd_io');
}

if (is_numeric($ss['ssRawContexts'])) {
    $os->enableGraph('ucd_contexts');
}

if (is_numeric($ss['ssRawInterrupts'])) {
    $os->enableGraph('ucd_interrupts');
}

if (is_numeric($ss['ssCpuRawWait'])) {
    $os->enableGraph('ucd_io_wait');
}

if (is_numeric($ss['ssCpuRawSteal'])) {
    $os->enableGraph('ucd_cpu_steal');
}

//
// Poll laLoadInt for load averages on UNIX-like hosts running UCD/Net-SNMPd
// UCD-SNMP-MIB::laLoadInt.1 = INTEGER: 206
// UCD-SNMP-MIB::laLoadInt.2 = INTEGER: 429
// UCD-SNMP-MIB::laLoadInt.3 = INTEGER: 479
$load_raw = snmp_get_multi($device, ['laLoadInt.1', 'laLoadInt.2', 'laLoadInt.3'], '-OQUs', 'UCD-SNMP-MIB');

// Check to see that the 5-min OID is actually populated before we make the rrd
if (is_numeric($load_raw[2]['laLoadInt'])) {
    $rrd_def = RrdDefinition::make()
        ->addDataset('1min', 'GAUGE', 0)
        ->addDataset('5min', 'GAUGE', 0)
        ->addDataset('15min', 'GAUGE', 0);

    $fields = [
        '1min'   => $load_raw[1]['laLoadInt'],
        '5min'   => $load_raw[2]['laLoadInt'],
        '15min'  => $load_raw[3]['laLoadInt'],
    ];

    $tags = compact('rrd_def');
    data_update($device, 'ucd_load', $tags, $fields);

    $os->enableGraph('ucd_load');
}

unset($ss, $load_raw, $snmpdata);
unset($key, $collect_oids, $rrd_name, $rrd_def, $oid);
