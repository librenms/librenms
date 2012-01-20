<?php

if ($config['enable_printers'])
{
  $toner_data = dbFetchRows("SELECT * FROM toner WHERE device_id = ?", array($device['device_id']));

  foreach ($toner_data as $toner)
  {
    echo("Checking toner " . $toner['toner_descr'] . "... ");

    $tonerperc = round(snmp_get($device, $toner['toner_oid'], "-OUqnv") / $toner['toner_capacity'] * 100);
    # FIXME also repoll capacity, or tonerperc can be incorrect; then line below can go too
    if ($tonerperc > 100) { $tonerperc = 100; } # ^

    $old_tonerrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("toner-" . $toner['toner_descr'] . ".rrd");
    $tonerrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("toner-" . $toner['toner_index'] . ".rrd");

    if (!is_file($tonerrrd) && is_file($old_tonerrrd))
    {
      rename($old_tonerrrd, $tonerrrd);
    }

    if (!is_file($tonerrrd))
    {
      rrdtool_create($tonerrrd,"--step 300 \
      DS:toner:GAUGE:600:0:20000 \
      RRA:AVERAGE:0.5:1:1200 \
      RRA:MIN:0.5:12:2400 \
      RRA:MAX:0.5:12:2400 \
      RRA:AVERAGE:0.5:12:2400");
    }

    echo($tonerperc . " %\n");

    rrdtool_update($tonerrrd,"N:$tonerperc");

    #FIXME should report for toner out... :)

    # Log toner swap
    if ($tonerperc > $toner['toner_current'])
    {
      log_event('Toner ' . $toner['toner_descr'] . ' was replaced (new level: ' . $tonerperc . '%)', $device, 'toner', $toner['toner_id']);
    }

    dbUpdate(array('toner_current' => $tonerperc, 'toner_capacity' => $toner['toner_capacity']), 'toner', '`toner_id` = ?', array($toner['toner_id']));
  }
}

?>
