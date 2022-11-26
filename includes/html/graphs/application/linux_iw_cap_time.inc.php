<?php

$unit_text = 'ms';
$print_format = '%8.0lf';
$lower_limit = 0;

$rrdArray = [
    'connected_time' => ['descr' => 'Connected Time', 'multiplier' => 1000],
    'inactive_time' => ['descr' => 'Inactive Time'],
    'rx_duration' => ['descr' => 'RX Duration', 'divider' => 1000],
];

require 'includes/html/graphs/application/linux_iw-common_cap.inc.php';

require 'includes/html/graphs/application/linux_iw-common.inc.php';

require 'includes/html/graphs/application/linux_iw-common_multi_line.inc.php';

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
