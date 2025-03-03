<?php

$filename = Rrd::name($device['hostname'], 'hr_users');

$ds = 'users';

$unit_text = 'Users';

$float_precision = 3;

$descr = '';

require 'includes/html/graphs/generic_stats.inc.php';
