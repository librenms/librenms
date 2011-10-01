<?php

include("includes/graphs/common.inc.php");

$scale_min    = 0;
$colours      = "red";
$nototal      = (($width<224) ? 1 : 0);
$unit_text    = "Packets/sec";
$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-powerdns-".$app['app_id'].".rrd";
$array        = array(
                      'corruptPackets' => array('descr' => 'Corrupt', 'colour' => 'FF8800FF'),
                      'servfailPackets' => array('descr' => 'Failed', 'colour' => 'FF0000FF'),
                      'q_timedout' => array('descr' => 'Timedout', 'colour' => 'FFFF00FF'),
                     );

$i            = 0;

if (is_file($rrd_filename))
{
  foreach ($array as $ds => $vars)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr']    = $vars['descr'];
    $rrd_list[$i]['ds']       = $ds;
    $rrd_list[$i]['colour']   = $vars['colour'];
    $i++;
  }
} else {
  echo("file missing: $file");
}

include("includes/graphs/generic_multi_simplex_seperated.inc.php");

?>
