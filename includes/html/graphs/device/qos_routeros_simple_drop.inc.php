<?php

$rrd_filename = Rrd::name($device['hostname'], ['routeros-simplequeue', $vars['rrd_id']]);

$ds_in = 'dropbytesin';
$ds_out = 'dropbytesout';

$multiplier = 8;

require 'includes/html/graphs/generic_data.inc.php';
