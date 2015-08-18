<?php

// alpha:/home/observium/dev# snmpbulkwalk -v2c -c XXXXX -M mibs -m CISCO-IPSEC-FLOW-MONITOR-MIB cisco.3925  cipSecGlobalStats
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalActiveTunnels.0 = Gauge32: 10
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalPreviousTunnels.0 = Counter32: 677 Phase-2 Tunnels
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInOctets.0 = Counter32: 2063116135 Octets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalHcInOctets.0 = Counter64: 135207102311
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInOctWraps.0 = Counter32: 31 Integral units
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInDecompOctets.0 = Counter32: 2063116135 Octets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalHcInDecompOctets.0 = Counter64: 135207102311
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInDecompOctWraps.0 = Counter32: 31 Integral units
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInPkts.0 = Counter32: 779904964 Packets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInDrops.0 = Counter32: 5 Packets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInReplayDrops.0 = Counter32: 32 Packets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInAuths.0 = Counter32: 779904970 Events
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInAuthFails.0 = Counter32: 0 Failures
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInDecrypts.0 = Counter32: 779904970 Packets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInDecryptFails.0 = Counter32: 5 Packets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutOctets.0 = Counter32: 3486168696 Octets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalHcOutOctets.0 = Counter64: 544652047992
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutOctWraps.0 = Counter32: 126 Integral units
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutUncompOctets.0 = Counter32: 3486168696 Octets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalHcOutUncompOctets.0 = Counter64: 544652047992 Octets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutUncompOctWraps.0 = Counter32: 126 Integral units
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutPkts.0 = Counter32: 828696339 Packets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutDrops.0 = Counter32: 4520 Packets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutAuths.0 = Counter32: 828696339 Events
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutAuthFails.0 = Counter32: 0 Failures
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutEncrypts.0 = Counter32: 828696318 Packets
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutEncryptFails.0 = Counter32: 0 Failures
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalProtocolUseFails.0 = Counter32: 0 Failures
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalNoSaFails.0 = Counter32: 5 Failures
// CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalSysCapFails.0 = Counter32: 0 Failures
if ($device['os_group'] == 'cisco') {
    $data = snmpwalk_cache_oid($device, 'cipSecGlobalStats', null, 'CISCO-IPSEC-FLOW-MONITOR-MIB');
    $data = $data[0];

    // Use HC Counters if they exist
    if (is_numeric($data['cipSecGlobalHcInOctets'])) {
        $data['cipSecGlobalInOctets'] = $data['cipSecGlobalHcInOctets'];
    }

    if (is_numeric($data['cipSecGlobalHcOutOctets'])) {
        $data['cipSecGlobalOutOctets'] = $data['cipSecGlobalHcOutOctets'];
    }

    if (is_numeric($data['cipSecGlobalHcInDecompOctets'])) {
        $data['cipSecGlobalInDecompOctets'] = $data['cipSecGlobalHcInDecompOctets'];
    }

    if (is_numeric($data['cipSecGlobalHcOutUncompOctets'])) {
        $data['cipSecGlobalOutUncompOctets'] = $data['cipSecGlobalHcOutUncompOctets'];
    }

    $rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('cipsec_flow.rrd');
    $rrd_create   = ' DS:Tunnels:GAUGE:600:0:U';
    $rrd_create  .= ' DS:InOctets:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:OutOctets:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:InDecompOctets:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:OutUncompOctets:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:InPkts:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:OutPkts:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:InDrops:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:InReplayDrops:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:OutDrops:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:InAuths:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:OutAuths:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:InAuthFails:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:OutAuthFails:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:InDencrypts:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:OutEncrypts:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:InDecryptFails:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:OutEncryptFails:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:ProtocolUseFails:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:NoSaFails:COUNTER:600:0:100000000000';
    $rrd_create  .= ' DS:SysCapFails:COUNTER:600:0:100000000000';
    $rrd_create  .= $config['rrd_rra'];

    if (is_file($rrd_filename) || $data['cipSecGlobalActiveTunnels']) {
        if (!file_exists($rrd_filename)) {
            rrdtool_create($rrd_filename, $rrd_create);
        }

        $fields = array(
            'Tunnels'          => $data['cipSecGlobalActiveTunnels'],
            'InOctets'         => $data['cipSecGlobalInOctets'],
            'OutOctets'        => $data['cipSecGlobalOutOctets'],
            'InDecompOctets'   => $data['cipSecGlobalInDecompOctets'],
            'OutUncompOctets'  => $data['cipSecGlobalOutUncompOctets'],
            'InPkts'           => $data['cipSecGlobalInPkts'],
            'OutPkts'          => $data['cipSecGlobalOutPkts'],
            'InDrops'          => $data['cipSecGlobalInDrops'],
            'InReplayDrops'    => $data['cipSecGlobalInReplayDrops'],
            'OutDrops'         => $data['cipSecGlobalOutDrops'],
            'InAuths'          => $data['cipSecGlobalInAuths'],
            'OutAuths'         => $data['cipSecGlobalOutAuths'],
            'InAuthFails'      => $data['cipSecGlobalInAuthFails'],
            'OutAuthFails'     => $data['cipSecGlobalOutAuthFails'],
            'InDencrypts'      => $data['cipSecGlobalInDecrypts'],
            'OutEncrypts'      => $data['cipSecGlobalOutEncrypts'],
            'InDecryptFails'   => $data['cipSecGlobalInDecryptFails'],
            'OutEncryptFails'  => $data['cipSecGlobalOutEncryptFails'],
            'ProtocolUseFails' => $data['cipSecGlobalProtocolUseFails'],
            'NoSaFails'        => $data['cipSecGlobalNoSaFails'],
            'SysCapFails'      => $data['cipSecGlobalSysCapFails'],
        );
        rrdtool_update($rrd_filename, $fields);

        $graphs['cipsec_flow_tunnels'] = true;
        $graphs['cipsec_flow_pkts']    = true;
        $graphs['cipsec_flow_bits']    = true;
        $graphs['cipsec_flow_stats']   = true;

        echo ' cipsec_flow';
    }//end if

    unset($data, $rrd_filename, $rrd_create, $rrd_update);
}//end if
