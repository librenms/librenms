<?php

$qos = \App\Models\Qos::find($vars['qos_id']);
$rrd_filename = Rrd::name($device['hostname'], ['routeros-simplequeue', $qos->rrd_id]);

$colour_area_in = 'FF8888';
$colour_line_in = '880000';
$colour_area_out = 'FFE48F';
$colour_line_out = 'B68A00';

$ds_in = 'droppacketsin';
$ds_out = 'droppacketsout';

$unit_text = 'packets/s';

require 'includes/html/graphs/generic_duplex.inc.php';
