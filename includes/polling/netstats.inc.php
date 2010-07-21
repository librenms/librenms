<?php

if($device[os] != "Snom") {

  echo("Polling device network statistics...\n");

  #### These are at the start of large trees that we don't want to walk the entirety of, so we snmpget_multi them

  $oids['ip'] = array ('ipForwDatagrams','ipInDelivers','ipInReceives','ipOutRequests','ipInDiscards','ipOutDiscards','ipOutNoRoutes',
                 'ipReasmReqds','ipReasmOKs','ipReasmFails','ipFragOKs','ipFragFails','ipFragCreates', 'ipInUnknownProtos',
                 'ipInHdrErrors', 'ipInAddrErrors');

  $oids['tcp'] = array ('tcpActiveOpens', 'tcpPassiveOpens', 'tcpAttemptFails', 'tcpEstabResets', 'tcpCurrEstab',
    'tcpInSegs', 'tcpOutSegs', 'tcpRetransSegs', 'tcpInErrs', 'tcpOutRsts');

  $oids['udp'] = array ('udpInDatagrams','udpOutDatagrams','udpInErrors','udpNoPorts');

  $oids['tcp_collect'] = $oids['tcp'];
  $oids['tcp_collect'][] = 'tcpHCInSegs';
  $oids['tcp_collect'][] = 'tcpHCOutSegs';

  #### Below have more oids, and are in trees by themselves, so we can snmpwalk_cache_oid them

  $oids['icmp'] =   array('icmpInMsgs','icmpOutMsgs','icmpInErrors','icmpOutErrors','icmpInEchos','icmpOutEchos','icmpInEchoReps',
                 'icmpOutEchoReps','icmpInDestUnreachs','icmpOutDestUnreachs','icmpInParmProbs','icmpInTimeExcds',
                 'icmpInSrcQuenchs','icmpInRedirects','icmpInTimestamps','icmpInTimestampReps','icmpInAddrMasks',
                 'icmpInAddrMaskReps','icmpOutTimeExcds','icmpOutParmProbs','icmpOutSrcQuenchs','icmpOutRedirects',
                 'icmpOutTimestamps','icmpOutTimestampReps','icmpOutAddrMasks','icmpOutAddrMaskReps');

  $oids['snmp'] = array ('snmpInPkts','snmpOutPkts','snmpInBadVersions','snmpInBadCommunityNames','snmpInBadCommunityUses','snmpInASNParseErrs',
   'snmpInTooBigs','snmpInNoSuchNames','snmpInBadValues','snmpInReadOnlys','snmpInGenErrs','snmpInTotalReqVars','snmpInTotalSetVars',
   'snmpInGetRequests','snmpInGetNexts','snmpInSetRequests','snmpInGetResponses','snmpInTraps','snmpOutTooBigs','snmpOutNoSuchNames',
   'snmpOutBadValues','snmpOutGenErrs','snmpOutGetRequests','snmpOutGetNexts','snmpOutSetRequests','snmpOutGetResponses','snmpOutTraps','snmpSilentDrops','snmpProxyDrops');

  $protos = array('ip','icmp', 'snmp', 'udp', 'tcp');

  foreach($protos as $proto) {
    unset($snmpstring, $rrdupdate, $snmpdata, $snmpdata_cmd, $rrd_create);
    $rrdfile = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("netstats-".$proto.".rrd");

    $rrd_create = $config['rrdtool'] . " create $rrdfile ";
    $rrd_create .= "RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 \
                    RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797";

    foreach($oids[$proto] as $oid){
      $oid_ds = truncate($oid, 19, '');
      $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";
      $snmpstring .= " $oid.0"; 
    }
    if(!file_exists($rrdfile)) { shell_exec($rrd_create); }

    if($proto == "ip" || $proto == "tcp" || $proto == "udp")
    {
      $data = snmp_get_multi($device ,$snmpstring);
    } else {
      $data_array = snmpwalk_cache_oid($proto, $device, array());
    }
    
    $rrdupdate = "N";

    foreach($oids[$proto] as $oid){
      if(is_numeric($data[0][$oid])) 
      { 
        $value = $data[0][$oid]; 
      } elseif(is_numeric($data_array[$device['device_id']][0][$oid])) {
         $value = $data_array[$device['device_id']][0][$oid];
      } else { 
        $value = "0"; 
      }
      $rrdupdate .= ":$value";
    }

    unset($snmpstring);
    rrdtool_update($rrdfile, $rrdupdate);
  }

}

?>
