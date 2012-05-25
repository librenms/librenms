<?php

/// Cycle through dot3stats OIDs and build list of RRAs to pass to multi simplex grapher

$oids = array('dot3StatsAlignmentErrors', 'dot3StatsFCSErrors', 'dot3StatsSingleCollisionFrames', 'dot3StatsMultipleCollisionFrames',
              'dot3StatsSQETestErrors', 'dot3StatsDeferredTransmissions', 'dot3StatsLateCollisions', 'dot3StatsExcessiveCollisions',
              'dot3StatsInternalMacTransmitErrors', 'dot3StatsCarrierSenseErrors', 'dot3StatsFrameTooLongs', 'dot3StatsInternalMacReceiveErrors',
              'dot3StatsSymbolErrors');

$i = 0;
$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("port-" . $port['ifIndex'] . "-dot3.rrd");

if (is_file($rrd_filename))
{
  foreach ($oids as $oid)
  {
    $oid = str_replace("dot3Stats", "", $oid);
    $oid_ds = truncate($oid, 19, '');
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $oid;
    $rrd_list[$i]['ds'] = $oid_ds;
    $i++;
  }
}
#} else { echo("file missing: $file");  }

$colours   = "mixed";
$nototal   = 1;
$unit_text = "Errors/sec";
$simple_rrd = 1;

include("includes/graphs/generic_multi_simplex_seperated.inc.php");

?>
