<?php

$unit_text = 'Mbit/s';
$lower_limit = 0;

$rrdArray = [
    'rx_bitrate' => ['descr' => 'RX Bitrate'],
    'tx_bitrate' => ['descr' => 'TX Bitrate'],
];

require 'includes/html/graphs/application/linux_iw-common_cap.inc.php';

require 'includes/html/graphs/application/linux_iw-common.inc.php';

require 'includes/html/graphs/application/linux_iw-common_multi_line.inc.php';

require 'includes/html/graphs/generic_multi_line.inc.php';
