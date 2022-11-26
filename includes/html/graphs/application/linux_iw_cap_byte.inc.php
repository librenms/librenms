<?php

$unit_text = 'Bytes';
$format = 'bytes';

$ds_in = 'rx_bytes';
$in_text = 'Rcvd';
$ds_out = 'tx_bytes';
$out_text = 'Sent';

require 'includes/html/graphs/application/linux_iw-common_cap.inc.php';

require 'includes/html/graphs/application/linux_iw-common.inc.php';

require 'includes/html/graphs/application/linux_iw-common_duplex.inc.php';

require 'includes/html/graphs/generic_duplex.inc.php';
