<?php

#print_r($_GET);

if($_GET['id']) { $id = mres($_GET['id']); }
if($_GET['device']) { $id = mres($_GET['device']); }

$i=1;

$query = mysql_query("SELECT * FROM `ucd_diskio` AS U, `devices` AS D WHERE D.device_id = '".$id."' AND U.device_id = D.device_id");
while($disk = mysql_fetch_array($query)) {
  $rrd_filename = $config['rrd_dir'] . "/" . $disk['hostname'] . "/ucd_diskio-" . safename($disk['diskio_descr'] . ".rrd");
  if(is_file($rrd_filename))
  {
    $rrd_list[$i]['filename'] = $rrd_filename;
    $rrd_list[$i]['descr'] = $disk['diskio_descr'];
    $rrd_list[$i]['rra_in'] = "reads";
    $rrd_list[$i]['rra_out'] = "writes";
    $i++;
  }
}

$units='';
$units_descr='Operations/sec';
$total_units='B';
$colours_in='greens';
$multiplier = "1";
$colours_out = 'blues';

$nototal = 1;


include ("generic_multi_seperated.inc.php");

?>
