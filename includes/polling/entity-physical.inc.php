<?php

echo("Entity Physical: ");

if($device['os'] == "ios")
{

 echo("Cisco Cat6xxx/76xx Crossbar : \n");

 $mod_stats  = snmpwalk_cache_oid($device, "cc6kxbarModuleModeTable", array(), "CISCO-CAT6K-CROSSBAR-MIB");
 $chan_stats = snmpwalk_cache_oid($device, "cc6kxbarModuleChannelTable", array(), "CISCO-CAT6K-CROSSBAR-MIB");
 $chan_stats = snmpwalk_cache_oid($device, "cc6kxbarStatisticsTable", $chan_stats, "CISCO-CAT6K-CROSSBAR-MIB");

 foreach ($mod_stats as $index => $entry)
 {
   $group = 'c6kxbar';
   foreach ($entry as $key => $value)
   {
     $subindex = NULL;
     $entPhysical_state[$index][$subindex][$group][$key] = $value;
   }
 }

 foreach ($chan_stats as $index => $entry)
 {
   list($index,$subindex) = explode(".", $index, 2);
   $group = 'c6kxbar';
   foreach ($entry as $key => $value)
   {
     $entPhysical_state[$index][$subindex][$group][$key] = $value;
   }

   $chan_update = $entry['cc6kxbarStatisticsInUtil'];
   $chan_update .= ":".$entry['cc6kxbarStatisticsOutUtil'];
   $chan_update .= ":".$entry['cc6kxbarStatisticsOutDropped'];
   $chan_update .= ":".$entry['cc6kxbarStatisticsOutErrors'];
   $chan_update .= ":".$entry['cc6kxbarStatisticsInErrors'];

   $rrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("c6kxbar-".$index."-".$subindex.".rrd");

   if ($debug) { echo("$rrd "); }

   if (!is_file($rrd))
   {
     rrdtool_create ($rrd, "--step 300 \
     DS:inutil:GAUGE:600:0:100 \
     DS:oututil:GAUGE:600:0:100 \
     DS:outdropped:DERIVE:600:0:125000000000 \
     DS:outerrors:DERIVE:600:0:125000000000 \
     DS:inerrors:DERIVE:600:0:125000000000 ".$config['rrd_rra']);
   }

   rrdtool_update($rrd,"N:$chan_update");

 }

#print_r($entPhysical_state);

}

// Set Entity state
foreach (dbFetch("SELECT * FROM `entPhysical_state` WHERE `device_id` = ?", array($device['device_id'])) as $entity)
{
  if (!isset($entPhysical_state[$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']]))
  {
    dbDelete('entPhysical_state', "`device_id` = ? AND `entPhysicalIndex` = ? AND `subindex` = ? AND `group` = ? AND `key` = ?",
                               array($device['device_id'], $entity['entPhysicalIndex'], $entity['subindex'], $entity['group'], $entity['key']));
  } else {
    if ($entPhysical_state[$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']] != $entity['value'])
    {
      echo("no match!");
    }
    unset($entPhysical_state[$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']]);
  }
}
// End Set Entity Attrivs

// Delete Entity state
foreach ($entPhysical_state as $epi => $entity)
{
  foreach ($entity as $subindex => $si)
  {
    foreach ($si as $group => $ti)
    {
      foreach ($ti as $key => $value)
      {
        dbInsert(array('device_id' => $device['device_id'], 'entPhysicalIndex' => $epi, 'subindex' => $subindex, 'group' => $group, 'key' => $key, 'value' => $value), 'entPhysical_state');
      }
    }
  }
}
// End Delete Entity state

echo("\n");

?>
