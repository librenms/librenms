<?php

## Generate a list of ports and then call the multi_bits grapher to generate from the list

$query = mysql_query("SELECT * FROM `ports` AS I, `devices` AS D WHERE I.interface_id = '".mres($_GET['port'])."'
                      AND I.device_id = D.device_id");
$port = mysql_fetch_array($query);

$oids = array('dot3StatsAlignmentErrors', 'dot3StatsFCSErrors', 'dot3StatsSingleCollisionFrames', 'dot3StatsMultipleCollisionFrames',
              'dot3StatsSQETestErrors', 'dot3StatsDeferredTransmissions', 'dot3StatsLateCollisions', 'dot3StatsExcessiveCollisions',
              'dot3StatsInternalMacTransmitErrors', 'dot3StatsCarrierSenseErrors', 'dot3StatsFrameTooLongs', 'dot3StatsInternalMacReceiveErrors',
              'dot3StatsSymbolErrors');

$i=0;
if(is_file($config['rrd_dir'] . "/" . $port['hostname'] . "/" . safename("etherlike-" . $port['ifIndex'] . ".rrd"))) {
  foreach($oids as $oid){
    $oid = str_replace("dot3Stats", "", $oid);
    $oid_rra = truncate($oid, 19, '');
    $rrd_create .= " DS:$oid:COUNTER:600:U:100000000000";
    $rrd_list[$i]['filename'] = $config['rrd_dir'] . "/" . $port['hostname'] . "/" . safename("etherlike-" . $port['ifIndex'] . ".rrd");
    $rrd_list[$i]['descr'] = $oid;
    $rrd_list[$i]['rra'] = $oid_rra;
    $i++;
  }
}

$colours   = "mixed";
$nototal   = 1;
$unit_text = "Errors";

include ("generic_multi_simplex_seperated.inc.php");



?>
