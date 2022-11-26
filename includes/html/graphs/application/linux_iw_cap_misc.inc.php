<?php

$unit_text = '';
$lower_limit = 0;

$rrdArray = [
    'beacon_interval' => ['descr' => 'Beacon Interval'],
    'dtim_interval' => ['descr' => 'DTIM Interval'],
];

require 'includes/html/graphs/application/linux_iw-common_cap.inc.php';

require 'includes/html/graphs/application/linux_iw-common.inc.php';

require 'includes/html/graphs/application/linux_iw-common_multi_line.inc.php';

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
