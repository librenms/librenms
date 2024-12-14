<?php

$qos = \App\Models\Qos::find($vars['qos_id']);
$rrd_filename = Rrd::name($device['hostname'], ['routeros-simplequeue', $qos->rrd_id]);

$ds_in = 'bytesin';
$ds_out = 'bytesout';

$multiplier = 8;

require 'includes/html/graphs/generic_data.inc.php';
