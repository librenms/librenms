<?php
if (!is_array($storage_cache['scality'])) 
   {$storage_cache['scality'] = snmpwalk_group($device, 'ringEntry', 'SCALITY-MIB');
   echo("Array value below:\n ");
   echo($storage_cache['scality'][$storage['storage_index']]);
   echo("\n");
   echo($storage_cache['scality']["1"]["ringState"]);
   echo("\nScality Storage\n ");
   }
$entry = $storage_cache['scality'][1];
$storage['units'] = (1000 * 1000);
$storage['size'] = ($entry['ringStorageTotal'] * $storage['units']);
$storage['used'] = ($entry['ringStorageUsed'] * ($storage['units']));
$storage['free'] = ($entry['ringStorageAvailable'] * $storage['units']);
$storage['state'] = ($entry['ringState']);
