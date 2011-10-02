<?php

include("includes/graphs/common.inc.php");

$scale_min    = 0;
$nototal      = (($width < 550) ? 1 : 0);
$unit_text    = "Messages";
$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-mailscanner-" . $app['app_id'] . ".rrd";
$array        = array(
                      'spam' => array('descr' => 'Spam', 'colour' => 'FF8800'),
                      'virus' => array('descr' => 'Virus', 'colour' => 'FF0000')
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
