<?php

$unit_text = 'MHz';
$print_format = '%8.0lf';
$lower_limit = 0;

$rrdArray = [
    'center1' => ['descr' => 'Channel Center 1'],
    'center2' => ['descr' => 'Channel Center 2'],
    'channel' => ['descr' => 'Channel Floor'],
    'width' => ['descr' => 'Channel Width'],
    'channel_ceiling' => ['descr' => 'Channel Ceiling', 'cdef_rpn' => ['val1' => 'channel', 'val2' => 'width', 'oper' => '+']],
];

require 'includes/html/graphs/application/linux_iw-common_interface.inc.php';

require 'includes/html/graphs/application/linux_iw-common.inc.php';

require 'includes/html/graphs/application/linux_iw-common_multi_line.inc.php';

require 'includes/html/graphs/generic_multi_line_exact_numbers_allow_maths.inc.php';
