<?php

$nototal = 1;

$ds_in = 'msg_recv';
$ds_out = 'msg_sent';

$graph_titel .= '::messages';
$unit_text = 'Messages/sec';

$colour_line_in = '008800FF';
$colour_line_out = '000088FF';
$colour_area_in = 'CEFFCE66';
$colour_area_out = 'CECEFF66';
$colour_area_in_max = 'CC88CC';
$colour_area_out_max = 'FFEFAA';

$mailscanner_rrd = Rrd::name($device['hostname'], ['app', 'mailscannerV2', $app['app_id']]);

if (Rrd::checkRrdExists($mailscanner_rrd)) {
    $rrd_filename = $mailscanner_rrd;
}

require 'includes/html/graphs/generic_duplex.inc.php';
