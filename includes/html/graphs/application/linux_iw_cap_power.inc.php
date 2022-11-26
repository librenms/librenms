<?php

$unit_text = 'dBm';
$lower_limit = -120;

$rrdArray = [
    'signal' => ['descr' => 'Signal Strength'],
    'snr' => ['descr' => 'SNR (Signal-to-Noise Ratio)'],
];

require 'includes/html/graphs/application/linux_iw-common_cap.inc.php';

require 'includes/html/graphs/application/linux_iw-common.inc.php';

require 'includes/html/graphs/application/linux_iw-common_multi_line.inc.php';

require 'includes/html/graphs/generic_multi_line_exact_numbers.inc.php';
