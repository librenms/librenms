<?php

$drbd_dev = $app['app_instance'];
$drbd_data = $agent_data['drbd'][$drbd_dev];

$rrd_filename  = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-drbd-".$drbd_dev.".rrd";


  foreach(explode("|", $drbd_data) as $part)
  {
    list($stat, $val) = explode("=", $part);
    if(!empty($stat))
    {
      $drbd[$stat] = $val;
    }
  }

  if (!is_file($rrd_filename)) {
    rrdtool_create ($rrd_filename, "--step 300 \
        DS:ns:DERIVE:600:0:125000000000 \
        DS:nr:DERIVE:600:0:125000000000 \
        DS:dw:DERIVE:600:0:125000000000 \
        DS:dr:DERIVE:600:0:125000000000 \
        DS:al:DERIVE:600:0:125000000000 \
        DS:bm:DERIVE:600:0:125000000000 \
        DS:lo:GAUGE:600:0:125000000000 \
        DS:pe:GAUGE:600:0:125000000000 \
        DS:ua:GAUGE:600:0:125000000000 \
        DS:ap:GAUGE:600:0:125000000000 \
        DS:oos:GAUGE:600:0:125000000000 \
        RRA:AVERAGE:0.5:1:600 \
        RRA:AVERAGE:0.5:6:700 \
        RRA:AVERAGE:0.5:24:775 \
        RRA:AVERAGE:0.5:288:797 \
        RRA:MIN:0.5:1:600 \
        RRA:MIN:0.5:6:700 \
        RRA:MIN:0.5:24:775 \
        RRA:MIN:0.5:3:600 \
        RRA:MAX:0.5:1:600 \
        RRA:MAX:0.5:6:700 \
        RRA:MAX:0.5:24:775 \
        RRA:MAX:0.5:288:797");
  }

  rrdtool_update($rrd_filename, "N:".$drbd['ns'].":".$drbd['nr'].":".$drbd['dw'].":".$drbd['dr'].":".$drbd['al'].":".$drbd['bm'].":".$drbd['lo'].":".$drbd['pe'].":".$drbd['ua'].":".$drbd['ap'].":".$drbd['oop']);


?>
