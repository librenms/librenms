<?php

## Generate a list of ports and then call the multi_bits grapher to generate from the list

$device = device_by_id_cache($id);

foreach (dbFetchRows("SELECT * FROM `ports` WHERE `device_id` = ?", array($id)) as $port)
{
  $ignore = 0;
  if (is_array($config['device_traffic_iftype']))
  {
    foreach ($config['device_traffic_iftype'] as $iftype)
    {
      if (preg_match($iftype ."i", $port['ifType']))
      {
        $ignore = 1;
      }
    }
  }
  if (is_array($config['device_traffic_descr']))
  {
    foreach ($config['device_traffic_descr'] as $ifdescr)
    {
      if (preg_match($ifdescr."i", $port['ifDescr']) || preg_match($ifdescr."i", $port['ifName']) || preg_match($ifdescr."i", $port['portName']))
      {
        $ignore = 1;
      }
    }
  }

  $rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/port-" . safename($port['ifIndex'] . ".rrd");
  if ($ignore != 1 && is_file($rrd_filename))
  {
    $rrd_filenames[] = $rrd_filename;
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $port['ifDescr'];
    $rrd_list[$i]['ds_in'] = $ds_in;
    $rrd_list[$i]['ds_out'] = $ds_out;
    $i++;
  }

  unset($ignore);
}

$units ='b';
$total_units ='B';
$colours_in ='greens';
$multiplier = "8";
$colours_out = 'blues';

$nototal = 1;

$ds_in  = "INOCTETS";
$ds_out = "OUTOCTETS";

$graph_title .= "::bits";

$colour_line_in = "006600";
$colour_line_out = "000099";
$colour_area_in = "CDEB8B";
$colour_area_out = "C3D9FF";

include("includes/graphs/generic_multi_bits_separated.inc.php");

?>
