<?php

    if($array[$device[device_id]][$port[ifIndex]] && $port['ifType'] == "ethernetCsmacd") { // Check to make sure Port data is cached.

      $this_port = &$array[$device[device_id]][$port[ifIndex]];

      $rrdfile = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("etherlike-".$port['ifIndex'].".rrd");

      $rrd_create = $config['rrdtool'] . " create $rrdfile ";
      $rrd_create .= "RRA:AVERAGE:0.5:1:600 RRA:AVERAGE:0.5:6:700 RRA:AVERAGE:0.5:24:775 RRA:AVERAGE:0.5:288:797 RRA:MAX:0.5:1:600 \
                      RRA:MAX:0.5:6:700 RRA:MAX:0.5:24:775 RRA:MAX:0.5:288:797";

      if(!file_exists($rrdfile)) {
        foreach($etherlike_oids as $oid){
          $oid = truncate(str_replace("dot3Stats", "", $oid), 19, '');
          $rrd_create .= " DS:$oid:COUNTER:600:U:100000000000";
        }
        shell_exec($rrd_create);
      }

      $rrdupdate = "N";
      foreach($etherlike_oids as $oid) {
        $data = $this_port[$oid] + 0;
        $rrdupdate .= ":$data";
      }
      rrdtool_update($rrdfile, $rrdupdate);

      echo("EtherLike ");

    }

?>
