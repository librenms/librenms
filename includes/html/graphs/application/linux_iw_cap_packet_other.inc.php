<?php

$unit_text = 'Packets';
$unitlen = strlen($unit_text);

$nototal = 1;
$descr_len = 15;

$rrdArray = [
    'rx_drop_misc' => ['descr'  => 'RX Dropped Misc'],
    'tx_failed' => ['descr'  => 'TX Failed'],
    'tx_retries' => ['descr'  => 'TX Retries'],
];

require 'includes/html/graphs/application/linux_iw-common_cap.inc.php';

require 'includes/html/graphs/application/linux_iw-common.inc.php';

require 'includes/html/graphs/application/linux_iw-common_multi_line.inc.php';

require 'includes/html/graphs/generic_multi_simplex_seperated.inc.php';
