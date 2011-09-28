<?php

    $units		= "b";
    $total_units	= "B";
    $colours_in		= "greens";
    //$multiplier	= "0";
    $colours_out	= "blues";

    $nototal		= 1;

    $ds_in		= "traf_in";
    $ds_out		= "traf_out";

    $graph_title	.= "::bits";

    $colour_line_in	= "006600";
    $colour_line_out	= "000099";
    $colour_area_in	= "CDEB8B";
    $colour_area_out	= "C3D9FF";

    $rrddir		= $config['rrd_dir']."/".$device['hostname'];
    $hostname		= (isset($_GET['hostname']) ? $_GET['hostname'] : "unkown");
    $rrd_filename	= $rrddir."/app-shoutcast-".$app['app_id']."-".$hostname.".rrd";

    include("includes/graphs/generic_bits.inc.php");

?>
