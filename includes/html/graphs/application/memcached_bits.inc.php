<?php

require 'memcached.inc.php';
require 'includes/html/graphs/common.inc.php';

$multiplier = 8;

$ds_in = 'bytes_read';
$ds_out = 'bytes_written';

require 'includes/html/graphs/generic_data.inc.php';
