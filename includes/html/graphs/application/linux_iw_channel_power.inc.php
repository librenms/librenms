<?php

$unit_text = 'dBm';
$lower_limit = -120;

$rrdArray = [
    'noise' => ['descr' => 'Channel Noise'],
    'txpower' => ['descr' => 'Channel TXPower'],
];

require 'includes/html/graphs/application/linux_iw-common_interface.inc.php';

require 'includes/html/graphs/application/linux_iw-common.inc.php';

require 'includes/html/graphs/application/linux_iw-common_multi_line.inc.php';

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
