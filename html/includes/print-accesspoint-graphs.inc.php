<?php
global $config;


$graph_array['height'] = '100';
$graph_array['width']  = '215';
$graph_array['to']     = $config['time']['now'];
$graph_array['id']     = $ap['accesspoint_id'];
$graph_array['type']   = $graph_type;

require 'includes/print-graphrow.inc.php';
