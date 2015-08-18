<?php

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

    unset($snmpstring, $fields, $snmpdata, $snmpdata_cmd, $rrd_create);
    $rrd_file = $config['rrd_dir'].'/'.$device['hostname'].'/netscaler-stats-tcp.rrd';

    $rrd_create = $config['rrd_rra'];

    foreach ($oids_gauge as $oid) {
        $oid_ds      = truncate(str_replace('tcp', '', str_replace('Active', 'Ac', str_replace('Passive', 'Ps', str_replace('Zombie', 'Zom', $oid)))), 19, '');
        $rrd_create .= " DS:$oid_ds:GAUGE:600:U:100000000000";
    }

    foreach ($oids_counter as $oid) {
        $oid_ds          = truncate(str_replace('tcp', '', str_replace('Active', 'Ac', str_replace('Passive', 'Ps', str_replace('Zombie', 'Zom', $oid)))), 19, '');
        $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";
    }

    $data = snmpwalk_cache_oid($device, 'nsTcpStatsGroup', array(), 'NS-ROOT-MIB');

    $fields = array();

    foreach ($oids as $oid) {
        if (is_numeric($data[0][$oid])) {
            $rrdupdate = ':'.$data[0][$oid];
        }
        else {
            $rrdupdate = 'U';
        }
        $fields[$oid] = $rrdupdate;
    }

    if (!file_exists($rrd_file)) {
        rrdtool_create($rrd_file, $rrd_create);
    }

    rrdtool_update($rrd_file, $fields);
    $graphs['netscaler_tcp_conn'] = true;
    $graphs['netscaler_tcp_bits'] = true;
    $graphs['netscaler_tcp_pkts'] = true;
}//end if

unset($oids, $data, $data_array, $oid);
