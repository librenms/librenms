<?php

echo("Polling device network statistics...\n");

$oids = array ('ipForwDatagrams','ipInDelivers','ipInReceives','ipOutRequests','ipInDiscards','ipOutDiscards','ipOutNoRoutes',
               'icmpInMsgs','icmpOutMsgs','icmpInErrors','icmpOutErrors','icmpInEchos','icmpOutEchos','icmpInEchoReps',
               'icmpOutEchoReps','snmpInTotalReqVars','snmpInTotalSetVars','snmpInTraps','snmpOutTraps','snmpInPkts',
               'snmpOutPkts','snmpOutGetResponses','snmpOutSetRequests','tcpActiveOpens','tcpPassiveOpens','tcpAttemptFails',
               'tcpEstabResets','tcpInSegs','tcpOutSegs','tcpRetransSegs','udpInDatagrams','udpOutDatagrams','udpInErrors',
               'udpNoPorts');

$rrdfile = "rrd/" . $device['hostname'] . "-netinfo.rrd";

$rrd_create = "rrdtool create $rrdfile ";
$rrd_create .= "RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 \
                RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797";

unset($snmpstring, $rrdupdate, $snmpdata, $snmpdata_cmd, $rrd_create);

foreach($oids as $oid){
  $rrd_create .= " DS:$oid:COUNTER:600:U:100000000000";
  $snmpstring .= " $oid.0";
}

echo("$snmpstring");

if(!file_exists($rrdfile)) { `$rrd_create`; }

$snmpdata_cmd = "snmpget -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'] . " $snmpstring";
$snmpdata     = trim(`$snmpdata_cmd`);

#echo($snmpdata_cmd . "\n");

#echo($snmpdata . "\n");

$rrdupdate = "N";

foreach(explode("\n", $snmpdata) as $data) {
  if(strstr($data, "No") || strstr($data, "d") || strstr($data, "s")) { $data = ""; }
  $rrdupdate .= ":$data";
}

echo("$rrdupdate\n");

rrd_update($rrdfile, $rrdupdate);

rrd_error();

?>
