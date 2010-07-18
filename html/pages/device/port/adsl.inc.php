<?php

  if(file_exists($config['rrd_dir'] . "/" . $hostname . "/port-". $ifIndex . "-adsl.rrd")) {

    $iid = $id;
    echo("<div class=graphhead>ADSL Line Speed</div>");
    $graph_type = "port_adsl_speed";
    include("includes/print-interface-graphs.inc.php");

    echo("<div class=graphhead>ADSL Line Attenuation</div>");
    $graph_type = "port_adsl_attenuation";
    include("includes/print-interface-graphs.inc.php");

    echo("<div class=graphhead>ADSL Line SNR Margin</div>");
    $graph_type = "port_adsl_snr";
    include("includes/print-interface-graphs.inc.php");

    echo("<div class=graphhead>ADSL Output Powers</div>");
    $graph_type = "port_adsl_power";
    include("includes/print-interface-graphs.inc.php");


  }

#    $graph_array['height'] = "100";
#    $graph_array['width']  = "385";
#    $graph_array['to']     = $now;
#    $graph_array['port']   = $ports['fileserver'];
#    $graph_array['type']   = "port_bits";
#    $graph_array['from']   = $day;
#    $graph_array['legend'] = "no";

#    $graph_array['popup_title'] = "Central Fileserver";

#    print_graph_popup($graph_array);



?>
