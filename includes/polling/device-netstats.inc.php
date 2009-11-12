<?php

if($device[os] != "Snom") {

  echo("Polling device network statistics...\n");

  $oids['ip'] = array ('ipForwDatagrams','ipInDelivers','ipInReceives','ipOutRequests','ipInDiscards','ipOutDiscards','ipOutNoRoutes',
                 'ipReasmReqds','ipReasmOKs','ipReasmFails','ipFragOKs','ipFragFails','ipFragCreates', 'ipInUnknownProtos',
                 'ipInHdrErrors', 'ipInAddrErrors');

  $oids['icmp'] =   array('icmpInMsgs','icmpOutMsgs','icmpInErrors','icmpOutErrors','icmpInEchos','icmpOutEchos','icmpInEchoReps',
                 'icmpOutEchoReps','icmpInDestUnreachs','icmpOutDestUnreachs','icmpInParmProbs','icmpInTimeExcds',
                 'icmpInSrcQuenchs','icmpInRedirects','icmpInTimestamps','icmpInTimestampReps','icmpInAddrMasks',
                 'icmpInAddrMaskReps','icmpOutTimeExcds','icmpOutParmProbs','icmpOutSrcQuenchs','icmpOutRedirects',
                 'icmpOutTimestamps','icmpOutTimestampReps','icmpOutAddrMasks','icmpOutAddrMaskReps');

  $oids['snmp'] = array ('snmpInPkts','snmpOutPkts','snmpInBadVersions','snmpInBadCommunityNames','snmpInBadCommunityUses','snmpInASNParseErrs',
'snmpInTooBigs','snmpInNoSuchNames','snmpInBadValues','snmpInReadOnlys','snmpInGenErrs','snmpInTotalReqVars','snmpInTotalSetVars',
'snmpInGetRequests','snmpInGetNexts','snmpInSetRequests','snmpInGetResponses','snmpInTraps','snmpOutTooBigs','snmpOutNoSuchNames',
'snmpOutBadValues','snmpOutGenErrs','snmpOutGetRequests','snmpOutGetNexts','snmpOutSetRequests','snmpOutGetResponses','snmpOutTraps','snmpSilentDrops','snmpProxyDrops');

  $oids['tcp'] = array ('tcpActiveOpens', 'tcpPassiveOpens', 'tcpAttemptFails', 'tcpEstabResets', 'tcpCurrEstab', 
'tcpInSegs', 'tcpOutSegs', 'tcpRetransSegs', 'tcpInErrs', 'tcpOutRsts', 'tcpHCInSegs', 'tcpHCOutSegs');

  $oids['udp'] = array ('udpInDatagrams','udpOutDatagrams','udpInErrors','udpNoPorts');

  $protos = array('ip','icmp', 'snmp', 'udp', 'tcp');

  foreach($protos as $proto) {
    unset($snmpstring, $rrdupdate, $snmpdata, $snmpdata_cmd, $rrd_create);
    $rrdfile = $config['rrd_dir'] . "/" . $device['hostname'] . "/netstats-".$proto.".rrd";

    $rrd_create = $config['rrdtool'] . " create $rrdfile ";
    $rrd_create .= "RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 \
                    RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797";

    foreach($oids[$proto] as $oid){
      $oid_ds = truncate($oid, 19, '');
      $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";
      $snmpstring .= " $oid.0"; 
    }
    if(!file_exists($rrdfile)) { shell_exec($rrd_create); }
    $snmpdata_cmd = "snmpget -m IP-MIB:SNMPv2-MIB:UDP-MIB:TCP-MIB:IP-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " $snmpstring";
    $snmpdata     = trim(shell_exec($snmpdata_cmd));
    $rrdupdate = "N";
    foreach(explode("\n", $snmpdata) as $data) {
      if(strstr($data, "No") || strstr($data, "d") || strstr($data, "s")) { $data = "0"; }
      $rrdupdate .= ":$data";
    }
    unset($snmpstring);
    rrdtool_update($rrdfile, $rrdupdate);
  }

}

?>
