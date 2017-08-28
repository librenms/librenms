<?php

use LibreNMS\RRD\RrdDefinition;

if ($device['os'] == 'netscaler') {
    echo ' IP';

    // These are at the start of large trees that we don't want to walk the entirety of, so we snmp_get_multi them
    $oids_gauge = array(
        'tcpCurServerConn',
        'tcpCurClientConn',
        'tcpActiveServerConn',
        'tcpCurClientConnClosing',
        'tcpCurServerConnEstablished',
        'tcpCurClientConnOpening',
        'tcpCurClientConnEstablished',
        'tcpCurServerConnClosing',
        'tcpSpareConn',
        'tcpSurgeQueueLen',
        'tcpCurServerConnOpening',
        'tcpCurPhysicalServers',
        'tcpReuseHit',
    );

    $oids_counter = array(
        'tcpTotServerConnOpened',
        'tcpTotServerConnClosed',
        'tcpTotClientConnOpened',
        'tcpTotClientConnClosed',
        'tcpTotSyn',
        'tcpTotSynProbe',
        'tcpTotSvrFin',
        'tcpTotCltFin',
        'tcpTotRxPkts',
        'tcpTotRxBytes',
        'tcpTotTxPkts',
        'tcpTotTxBytes',
        'tcpWaitToSyn',
        'tcpTotZombieCltConnFlushed',
        'tcpTotZombieSvrConnFlushed',
        'tcpTotZombieHalfOpenCltConnFlushed',
        'tcpTotZombieHalfOpenSvrConnFlushed',
        'tcpTotZombieActiveHalfCloseCltConnFlushed',
        'tcpTotZombieActiveHalfCloseSvrConnFlushed',
        'tcpTotZombiePassiveHalfCloseCltConnFlushed',
        'tcpTotZombiePassiveHalfCloseSrvConnFlushed',
        'tcpErrBadCheckSum',
        'tcpErrSynInSynRcvd',
        'tcpErrSynInEst',
        'tcpErrSynGiveUp',
        'tcpErrSynSentBadAck',
        'tcpErrSynRetry',
        'tcpErrFinRetry',
        'tcpErrFinGiveUp',
        'tcpErrFinDup',
        'tcpErrRst',
        'tcpErrRstNonEst',
        'tcpErrRstOutOfWindow',
        'tcpErrRstInTimewait',
        'tcpErrSvrRetrasmit',
        'tcpErrCltRetrasmit',
        'tcpErrFullRetrasmit',
        'tcpErrPartialRetrasmit',
        'tcpErrSvrOutOfOrder',
        'tcpErrCltOutOfOrder',
        'tcpErrCltHole',
        'tcpErrSvrHole',
        'tcpErrCookiePktSeqReject',
        'tcpErrCookiePktSigReject',
        'tcpErrCookiePktSeqDrop',
        'tcpErrCookiePktMssReject',
        'tcpErrRetransmit',
        'tcpErrRetransmitGiveUp',
        'pcbTotZombieCall',
        'tcpTotSynHeld',
        'tcpTotSynFlush',
        'tcpTotFinWaitClosed',
        'tcpErrAnyPortFail',
        'tcpErrIpPortFail',
        'tcpErrSentRst',
        'tcpErrBadStateConn',
        'tcpErrFastRetransmissions',
        'tcpErrFirstRetransmissions',
        'tcpErrSecondRetransmissions',
        'tcpErrThirdRetransmissions',
        'tcpErrForthRetransmissions',
        'tcpErrFifthRetransmissions',
        'tcpErrSixthRetransmissions',
        'tcpErrSeventhRetransmissions',
        'tcpErrDataAfterFin',
        'tcpErrRstThreshold',
        'tcpErrOutOfWindowPkts',
        'tcpErrSynDroppedCongestion',
        'tcpWaitData',
        'tcpErrStrayPkt',
    );

    $oids = array_merge($oids_gauge, $oids_counter);

    $data = snmpwalk_cache_oid($device, 'nsTcpStatsGroup', array(), 'NS-ROOT-MIB');


    $shorten = array(
        'tcp',
        'Active',
        'Passive',
        'Zombie',
    );
    $short_replacement = array(
        '',
        'Ac',
        'Ps',
        'Zom',
    );

    $rrd_def = new RrdDefinition();
    foreach ($oids_gauge as $oid) {
        $oid_ds    = str_replace($shorten, $short_replacement, $oid);
        $rrd_def->addDataset($oid_ds, 'GAUGE', null, 100000000000);
    }
    foreach ($oids_counter as $oid) {
        $oid_ds    = str_replace($shorten, $short_replacement, $oid);
        $rrd_def->addDataset($oid_ds, 'COUNTER', null, 100000000000);
    }

    $fields = array();
    foreach ($oids as $oid) {
        if (is_numeric($data[0][$oid])) {
            $rrdupdate = ':'.$data[0][$oid];
        } else {
            $rrdupdate = 'U';
        }
        $fields[$oid] = $rrdupdate;
    }

    $tags = compact('rrd_def');
    data_update($device, 'netscaler-stats-tcp', $tags, $fields);

    $graphs['netscaler_tcp_conn'] = true;
    $graphs['netscaler_tcp_bits'] = true;
    $graphs['netscaler_tcp_pkts'] = true;
}//end if

unset($oids_gauge, $oids_counter, $oids, $data, $tags, $fields, $rrd_def, $shorten, $short_replacement);
