<?php

#alpha:/home/observium/dev# snmpbulkwalk -v2c -c XXXXX -M mibs -m CISCO-IPSEC-FLOW-MONITOR-MIB cisco.3925  cipSecGlobalStats
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalActiveTunnels.0 = Gauge32: 10
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalPreviousTunnels.0 = Counter32: 677 Phase-2 Tunnels
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInOctets.0 = Counter32: 2063116135 Octets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalHcInOctets.0 = Counter64: 135207102311
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInOctWraps.0 = Counter32: 31 Integral units
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInDecompOctets.0 = Counter32: 2063116135 Octets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalHcInDecompOctets.0 = Counter64: 135207102311
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInDecompOctWraps.0 = Counter32: 31 Integral units
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInPkts.0 = Counter32: 779904964 Packets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInDrops.0 = Counter32: 5 Packets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInReplayDrops.0 = Counter32: 32 Packets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInAuths.0 = Counter32: 779904970 Events
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInAuthFails.0 = Counter32: 0 Failures
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInDecrypts.0 = Counter32: 779904970 Packets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalInDecryptFails.0 = Counter32: 5 Packets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutOctets.0 = Counter32: 3486168696 Octets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalHcOutOctets.0 = Counter64: 544652047992
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutOctWraps.0 = Counter32: 126 Integral units
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutUncompOctets.0 = Counter32: 3486168696 Octets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalHcOutUncompOctets.0 = Counter64: 544652047992 Octets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutUncompOctWraps.0 = Counter32: 126 Integral units
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutPkts.0 = Counter32: 828696339 Packets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutDrops.0 = Counter32: 4520 Packets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutAuths.0 = Counter32: 828696339 Events
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutAuthFails.0 = Counter32: 0 Failures
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutEncrypts.0 = Counter32: 828696318 Packets
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalOutEncryptFails.0 = Counter32: 0 Failures
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalProtocolUseFails.0 = Counter32: 0 Failures
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalNoSaFails.0 = Counter32: 5 Failures
#CISCO-IPSEC-FLOW-MONITOR-MIB::cipSecGlobalSysCapFails.0 = Counter32: 0 Failures

if ($device['os_group'] == "cisco")
{
  $data = snmpwalk_cache_oid($device, "cipSecGlobalStats", NULL, "CISCO-IPSEC-FLOW-MONITOR-MIB");
  $data = $data[0];

  /// Use HC Counters if they exist
  if (is_numeric($data['cipSecGlobalHcInOctets'])) { $data['cipSecGlobalInOctets']= $data['cipSecGlobalHcInOctets']; }
  if (is_numeric($data['cipSecGlobalHcOutOctets'])) { $data['cipSecGlobalOutOctets'] = $data['cipSecGlobalHcOutOctets']; }
  if (is_numeric($data['cipSecGlobalHcInDecompOctets'])) { $data['cipSecGlobalInDecompOctets'] = $data['cipSecGlobalHcInDecompOctets']; }
  if (is_numeric($data['cipSecGlobalHcOutUncompOctets'])) { $data['cipSecGlobalOutUncompOctets'] = $data['cipSecGlobalHcOutUncompOctets']; }

  $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("cipsec_flow.rrd");
  $rrd_create = " DS:Tunnels:GAUGE:600:0:U";
  $rrd_create .= " DS:InOctets:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:OutOctets:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:InDecompOctets:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:OutUncompOctets:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:InPkts:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:OutPkts:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:InDrops:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:InReplayDrops:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:OutDrops:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:InAuths:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:OutAuths:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:InAuthFails:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:OutAuthFails:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:InDencrypts:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:OutEncrypts:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:InDecryptFails:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:OutEncryptFails:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:ProtocolUseFails:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:NoSaFails:COUNTER:600:0:100000000000";
  $rrd_create .= " DS:SysCapFails:COUNTER:600:0:100000000000";
  $rrd_create .= $config['rrd_rra'];

  if (is_file($rrd_filename) || $data['cipSecGlobalActiveTunnels'])
  {
    if (!file_exists($rrd_filename))
    {
      rrdtool_create($rrd_filename, $rrd_create);
    }

    $rrd_update   = array();
    $rrd_update[] = $data['cipSecGlobalActiveTunnels'];
    $rrd_update[] = $data['cipSecGlobalInOctets'];
    $rrd_update[] = $data['cipSecGlobalOutOctets'];
    $rrd_update[] = $data['cipSecGlobalInDecompOctets'];
    $rrd_update[] = $data['cipSecGlobalOutUncompOctets'];
    $rrd_update[] = $data['cipSecGlobalInPkts'];
    $rrd_update[] = $data['cipSecGlobalOutPkts'];
    $rrd_update[] = $data['cipSecGlobalInDrops'];
    $rrd_update[] = $data['cipSecGlobalInReplayDrops'];
    $rrd_update[] = $data['cipSecGlobalOutDrops'];
    $rrd_update[] = $data['cipSecGlobalInAuths'];
    $rrd_update[] = $data['cipSecGlobalOutAuths'];
    $rrd_update[] = $data['cipSecGlobalInAuthFails'];
    $rrd_update[] = $data['cipSecGlobalOutAuthFails'];
    $rrd_update[] = $data['cipSecGlobalInDecrypts'];
    $rrd_update[] = $data['cipSecGlobalOutEncrypts'];
    $rrd_update[] = $data['cipSecGlobalInDecryptFails'];
    $rrd_update[] = $data['cipSecGlobalOutEncryptFails'];
    $rrd_update[] = $data['cipSecGlobalProtocolUseFails'];
    $rrd_update[] = $data['cipSecGlobalNoSaFails'];
    $rrd_update[] = $data['cipSecGlobalSysCapFails'];

    rrdtool_update($rrd_filename, $rrd_update);

    $graphs['cipsec_flow_tunnels'] = TRUE;
    $graphs['cipsec_flow_pkts']    = TRUE;
    $graphs['cipsec_flow_bits']    = TRUE;
    $graphs['cipsec_flow_stats']   = TRUE;

    echo(" cipsec_flow");
  }

  unset($data, $rrd_filename, $rrd_create, $rrd_update);
}

?>
