<?php

include('includes/graphs/common.inc.php');

$rrd_filename = $config["rrd_dir"] . '/' . $device["hostname"] . '/app-mysql-'.$app["app_id"].'.rrd';

$array = array('TOC'  => array('descr' => 'Table Cache'),
               'OFs'  => array('descr' => 'Open Files'),
               'OTs'  => array('descr' => 'Open Tables'),
               'OdTs' => array('descr' => 'Opened Tables'),
);

$i = 0;
if (is_file($rrd_filename))
{
  foreach ($array as $ds => $vars)
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $vars['descr'];
    $rrd_list[$i]['ds'] = $ds;
#    $rrd_list[$i]['colour'] = $vars['colour'];
    $i++;
  }
} else { echo("file missing: $file");  }

$colours   = "mixed";
$nototal   = 1;
$unit_text = "";

include("includes/graphs/generic_multi_simplex_seperated.inc.php");

?>
