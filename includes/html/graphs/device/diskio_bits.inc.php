<?php

$units = 'bps';
$total_units = 'B';
$colours_in = 'greens';
$multiplier = '8';
$colours_out = 'blues';

$nototal = 1;
$ds_in = 'read';
$ds_out = 'written';

require 'includes/html/graphs/device/diskio_common.inc.php';

require 'includes/html/graphs/generic_multi_bits_separated.inc.php';
