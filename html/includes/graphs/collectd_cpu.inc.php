<?php

$cpu = only_alphanumeric($_GET['cpu']);

$dir = $config['collectd_dir'] . "/" . $hostname ."/cpu-" . $cpu;


$graph = graph_collectd_cpu ($dir, $graphfile, $from, $to, $width, $height, $title, $vertical);

?>
