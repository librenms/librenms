<?php

$unit_text = 'Packets';
$format = 'packets';

$ds_in = 'rx_packets';
$in_text = 'Rcvd';
$ds_out = 'tx_packets';
$out_text = 'Sent';

require 'includes/html/graphs/application/linux_iw-common_cap.inc.php';

require 'includes/html/graphs/application/linux_iw-common.inc.php';

require 'includes/html/graphs/application/linux_iw-common_duplex.inc.php';

require 'includes/html/graphs/generic_duplex.inc.php';
