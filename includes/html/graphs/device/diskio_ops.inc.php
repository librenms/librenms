<?php

$units = '';
$units_descr = 'Operations/sec';
$total_units = 'B';
$colours_in = 'greens';
$multiplier = '1';
$colours_out = 'blues';

$ds_in = 'reads';
$ds_out = 'writes';

$nototal = 1;

require 'includes/html/graphs/device/diskio_common.inc.php';

require 'includes/html/graphs/generic_multi_seperated.inc.php';
