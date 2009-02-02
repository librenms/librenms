<?php

if($device[os] != "Snom") {

  echo("Polling device network statistics...\n");
  unset($snmpstring, $rrdupdate, $snmpdata, $snmpdata_cmd, $rrd_create);

  $oids = array ('ipForwDatagrams','ipInDelivers','ipInReceives','ipOutRequests','ipInDiscards','ipOutDiscards','ipOutNoRoutes',
                 'icmpInMsgs','icmpOutMsgs','icmpInErrors','icmpOutErrors','icmpInEchos','icmpOutEchos','icmpInEchoReps',
                 'icmpOutEchoReps','snmpInTotalReqVars','snmpInTotalSetVars','snmpInTraps','snmpOutTraps','snmpInPkts',
                 'snmpOutPkts','snmpOutGetResponses','snmpOutSetRequests','tcpActiveOpens','tcpPassiveOpens','tcpAttemptFails',
                 'tcpEstabResets','tcpInSegs','tcpOutSegs','tcpRetransSegs','udpInDatagrams','udpOutDatagrams','udpInErrors',
                 'udpNoPorts');

  $rrdfile = $rrd_dir . "/" . $device['hostname'] . "/netinfo.rrd";

  $Orrdfile = "rrd/" . $device['hostname'] . "-netinfo.rrd";
  if(is_file($Orrdfile) && !is_file($rrdfile)) { rename($Orrdfile, $rrdfile); echo("Moving $Orrdfile to $rrdfile");  }

  $rrd_create = "rrdtool create $rrdfile ";
  $rrd_create .= "RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 \
                  RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797";

  foreach($oids as $oid){
    $rrd_create .= " DS:$oid:COUNTER:600:U:100000000000";
    $snmpstring .= " $oid.0"; 
  }

  if(!file_exists($rrdfile)) { `$rrd_create`; }
  $snmpdata_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " $snmpstring";
  $snmpdata     = trim(`$snmpdata_cmd`);
  $rrdupdate = "N";
  foreach(explode("\n", $snmpdata) as $data) {
    if(strstr($data, "No") || strstr($data, "d") || strstr($data, "s")) { $data = ""; }
    $rrdupdate .= ":$data";
  }
  rrdtool_update($rrdfile, $rrdupdate);
}

?>
