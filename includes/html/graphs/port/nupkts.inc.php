<?php

use LibreNMS\Config;

$rrd_file = get_port_rrdfile_path($device['hostname'], $port['port_id']);

// FIXME uhh..
if (1) {
    // $rrd_list[1]['filename'] = $rrd_file;
    // $rrd_list[1]['descr'] = $int['ifDescr'];
    // $rrd_list[1]['ds_in'] = "INNUCASTPKTS";
    // $rrd_list[1]['ds_out'] = "OUTNUCASTPKTS";
    // $rrd_list[1]['descr']   = "NonUnicast";
    // $rrd_list[1]['colour_area_in'] = "BB77BB";
    // $rrd_list[1]['colour_area_out'] = "FFDD88";
    $rrd_list[2]['filename']        = $rrd_file;
    $rrd_list[2]['descr']           = $int['ifDescr'];
    $rrd_list[2]['ds_in']           = 'INBROADCASTPKTS';
    $rrd_list[2]['ds_out']          = 'OUTBROADCASTPKTS';
    $rrd_list[2]['descr']           = 'Broadcast';
    $rrd_list[2]['colour_area_in']  = Config::get('graph_colours.ports.bpkts.area_in');
    $rrd_list[2]['colour_area_out'] = Config::get('graph_colours.ports.bpkts.area_out');

    $rrd_list[4]['filename']        = $rrd_file;
    $rrd_list[4]['descr']           = $int['ifDescr'];
    $rrd_list[4]['ds_in']           = 'INMULTICASTPKTS';
    $rrd_list[4]['ds_out']          = 'OUTMULTICASTPKTS';
    $rrd_list[4]['descr']           = 'Multicast';
    $rrd_list[4]['colour_area_in']  = Config::get('graph_colours.ports.mpkts.area_in');
    $rrd_list[4]['colour_area_out'] = Config::get('graph_colours.ports.mpkts.area_out');

    $units       = '';
    $units_descr = 'Packets';
    $total_units = 'pps';
    $colours_in  = 'purples';
    $multiplier  = '1';
    $colours_out = 'oranges';

    $nototal = 1;

    include 'includes/html/graphs/generic_multi_seperated.inc.php';
} elseif (rrdtool_check_rrd_exists($rrd_file)) {
    $rrd_filename = $rrd_file;

    $ds_in  = 'INNUCASTPKTS';
    $ds_out = 'OUTNUCASTPKTS';

    $colour_area_in  = Config::get('graph_colours.ports.nupkts.area_in');
    $colour_line_in  = Config::get('graph_colours.ports.nupkts.line_in');
    $colour_area_out = Config::get('graph_colours.ports.nupkts.area_out');
    $colour_line_out = Config::get('graph_colours.ports.nupkts.line_out');

    $colour_area_in_max  = Config::get('graph_colours.ports.nupkts.area_in_max');
    $colour_area_out_max = Config::get('graph_colours.ports.nupkts.area_out_max');

    $unit_text = 'Packets';

    $graph_max = 1;

    include 'includes/html/graphs/generic_duplex.inc.php';
}//end if
