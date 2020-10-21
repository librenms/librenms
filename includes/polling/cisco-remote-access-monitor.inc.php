<?php

use LibreNMS\RRD\RrdDefinition;

// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasNumSessions.0 = Gauge32: 7 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasNumPrevSessions.0 = Counter32: 22 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasNumUsers.0 = Gauge32: 7 Users
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasNumGroups.0 = Gauge32: 0 Groups
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasGlobalInPkts.0 = Counter64: 0 Packets
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasGlobalOutPkts.0 = Counter64: 0 Packets
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasGlobalInOctets.0 = Counter64: 0 Octets
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasGlobalInDecompOctets.0 = Counter64: 0 Octets
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasGlobalOutOctets.0 = Counter64: 0 Octets
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasGlobalOutUncompOctets.0 = Counter64: 0 Octets
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasGlobalInDropPkts.0 = Counter64: 0 Packets
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasGlobalOutDropPkts.0 = Counter64: 0 Packets
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasEmailNumSessions.0 = Gauge32: 0 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasEmailCumulateSessions.0 = Counter32: 0 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasEmailPeakConcurrentSessions.0 = Gauge32: 0 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasIPSecNumSessions.0 = Gauge32: 0 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasIPSecCumulateSessions.0 = Counter32: 0 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasIPSecPeakConcurrentSessions.0 = Gauge32: 0 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasL2LNumSessions.0 = Gauge32: 0 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasL2LCumulateSessions.0 = Counter32: 0 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasL2LPeakConcurrentSessions.0 = Gauge32: 0 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasLBNumSessions.0 = Gauge32: 0 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasLBCumulateSessions.0 = Counter32: 0 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasLBPeakConcurrentSessions.0 = Gauge32: 0 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasSVCNumSessions.0 = Gauge32: 7 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasSVCCumulateSessions.0 = Counter32: 53 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasSVCPeakConcurrentSessions.0 = Gauge32: 9 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasWebvpnNumSessions.0 = Gauge32: 7 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasWebvpnCumulateSessions.0 = Counter32: 29 Sessions
// CISCO-REMOTE-ACCESS-MONITOR-MIB::crasWebvpnPeakConcurrentSessions.0 = Gauge32: 9 Sessions
if ($device['os_group'] == 'cisco') {
    $oid_list = ['crasEmailNumSessions.0', 'crasIPSecNumSessions.0', 'crasL2LNumSessions.0', 'crasLBNumSessions.0', 'crasSVCNumSessions.0', 'crasWebvpnNumSessions.0'];
    $data = snmp_get_multi($device, $oid_list, '-OUQs', 'CISCO-REMOTE-ACCESS-MONITOR-MIB');
    $data = $data[0];

    // Some ASAs return 'No Such Object available on this agent at this OID'
    // for crasEmailNumSessions.0. Clamp this to 0.
    if (! is_numeric($data['crasEmailNumSessions'])) {
        $data['crasEmailNumSessions'] = 0;
    }

    if (is_numeric($data['crasEmailNumSessions']) && is_numeric($data['crasIPSecNumSessions']) && is_numeric($data['crasL2LNumSessions']) && is_numeric($data['crasLBNumSessions']) && is_numeric($data['crasSVCNumSessions']) && is_numeric($data['crasWebvpnNumSessions'])) {
        $rrd_def = RrdDefinition::make()
            ->addDataset('email', 'GAUGE', 0)
            ->addDataset('ipsec', 'GAUGE', 0)
            ->addDataset('l2l', 'GAUGE', 0)
            ->addDataset('lb', 'GAUGE', 0)
            ->addDataset('svc', 'GAUGE', 0)
            ->addDataset('webvpn', 'GAUGE', 0);

        $fields = [
            'email'   => $data['crasEmailNumSessions'],
            'ipsec'   => $data['crasIPSecNumSessions'],
            'l2l'     => $data['crasL2LNumSessions'],
            'lb'      => $data['crasLBNumSessions'],
            'svc'     => $data['crasSVCNumSessions'],
            'webvpn'  => $data['crasWebvpnNumSessions'],
        ];

        $tags = compact('rrd_def');
        data_update($device, 'cras_sessions', $tags, $fields);

        $os->enableGraph('cras_sessions');
    }

    unset($data, $rrd_def, $fields, $oid_list);
}//end if
