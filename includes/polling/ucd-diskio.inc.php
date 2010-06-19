<?php

$sql = "SELECT * FROM `ucd_diskio` WHERE `device_id`  = '".$device['device_id']."'";
if($debug) { echo("$sql"); }
$diskio_data = mysql_query($sql);

if(mysql_affected_rows()) {


  $diskio_cache = array();
  $diskio_cache = snmpwalk_cache_oid("diskIOEntry", $device, $diskio_cache);

  $diskio_cache = $diskio_cache[$device['device_id']];

  echo("Checking UCD DiskIO MIB: ");

  while($diskio = mysql_fetch_array($diskio_data)) {

    $index = $diskio['diskio_index'];

    $entry = $diskio_cache[$index];

    echo($diskio['diskio_descr'] . " ");

    if($debug) { print_r($entry); }

    $rrd_update = $entry['diskIONReadX'].":".$entry['diskIONWrittenX'];
    $rrd_update .= ":".$entry['diskIOReads'].":".$entry['diskIOWrites'];

    $rrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("ucd_diskio-" . $diskio['diskio_descr'] .".rrd");

    if($debug) { echo("$rrd "); }

    if (!is_file($rrd)) {

      rrdtool_create ($rrd, "--step 300 \
      DS:read:DERIVE:600:0:125000000000 \
      DS:written:DERIVE:600:0:125000000000 \
      DS:reads:DERIVE:600:0:125000000000 \
      DS:writes:DERIVE:600:0:125000000000 \
      RRA:AVERAGE:0.5:1:600 \
      RRA:AVERAGE:0.5:6:700 \
      RRA:AVERAGE:0.5:24:775 \
      RRA:AVERAGE:0.5:288:797 \
      RRA:MIN:0.5:1:600 \
      RRA:MIN:0.5:6:700 \
      RRA:MIN:0.5:24:775 \
      RRA:MIN:0.5:288:797 \
      RRA:MAX:0.5:1:600 \
      RRA:MAX:0.5:6:700 \
      RRA:MAX:0.5:24:775 \
      RRA:MAX:0.5:288:797");
    }

    rrdtool_update($rrd,"N:$rrd_update");
    unset($rrd_update);

  }

}

unset($diskio_data);
unset($diskio_cache);

?>
