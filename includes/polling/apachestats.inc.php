<?php

    echo("Collecting Apache statistics...");

    $apacherrd = "rrd/" . safename($device['hostname'] . "-apache.rrd");
    if(!is_file($apacherrd)) {
      $woo= `rrdtool create $apacherrd         \
           DS:bits:COUNTER:600:U:10000000   \
           DS:hits:COUNTER:600:U:10000000  \
           RRA:AVERAGE:0.5:1:800      \
           RRA:AVERAGE:0.5:6:700      \
           RRA:AVERAGE:0.5:24:775     \
           RRA:AVERAGE:0.5:288:797    \
           RRA:MAX:0.5:1:800          \
           RRA:MAX:0.5:6:700          \
           RRA:MAX:0.5:24:775         \
           RRA:MAX:0.5:288:797`;
    }

    $this_host = $device['hostname'];

    list($ahits,$abits) = explode("\n", `./get-apache.sh $this_host`);
    $abits = $abits * 8;

    rrdtool_update($apacherrd,"N:$abits:$ahits");

    echo("\n");


?>
