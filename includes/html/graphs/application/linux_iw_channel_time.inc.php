<?php

$unit_text = 'ms';
$print_format = '%8.0lf';
$lower_limit = 0;

$rrdArray = [
    'channel_active_time' => ['descr' => 'Channel Active Time'],
    'channel_busy_time' => ['descr' => 'Channel Busy Time'],
    'channel_receive_time' => ['descr' => 'Channel Receive Time'],
    'channel_transmit_time' => ['descr' => 'Channel Transmit Time'],
];

require 'includes/html/graphs/application/linux_iw-common_interface.inc.php';

require 'includes/html/graphs/application/linux_iw-common.inc.php';

require 'includes/html/graphs/application/linux_iw-common_multi_line.inc.php';

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
