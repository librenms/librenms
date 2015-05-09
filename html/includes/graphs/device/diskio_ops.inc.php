<?php

$units = '';
$units_descr = 'Operations/sec';
$total_units = 'B';
$colours_in = 'greens';
$multiplier = "1";
$colours_out = 'blues';

$ds_in  = "reads";
$ds_out = "writes";

$nototal = 1;

include("includes/graphs/device/diskio_common.inc.php");

include("includes/graphs/generic_multi_seperated.inc.php");

?>
