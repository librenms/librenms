<?php

use LibreNMS\Config;

$ds_in  = 'INUCASTPKTS';
$ds_out = 'OUTUCASTPKTS';

$colour_area_in  = Config::get('graph_colours.ports.upkts.area_in');
$colour_line_in  = Config::get('graph_colours.ports.upkts.line_in');
$colour_area_out = Config::get('graph_colours.ports.upkts.area_out');
$colour_line_out = Config::get('graph_colours.ports.upkts.line_out');

$colour_area_in_max  = Config::get('graph_colours.ports.upkts.area_in_max');
$colour_area_out_max = Config::get('graph_colours.ports.upkts.area_out_max');

$graph_max = 1;
$unit_text = 'Packets';

require 'includes/html/graphs/generic_duplex.inc.php';
