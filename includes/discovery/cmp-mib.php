<?php

echo("OLD-CISCO-MEMORY-POOL: ");

$cmp_oids = array('ciscoMemoryPoolName','ciscoMemoryPoolAlternate','ciscoMemoryPoolValid','ciscoMemoryPoolUsed','ciscoMemoryPoolFree','ciscoMemoryPoolLargestFree');

foreach ($cmp_oids as $oid) { echo("$oid "); $cmp_array = snmp_cache_oid($oid, $device, $cmp_array, "CISCO-MEMORY-POOL-MIB"); }

foreach($cmp_array[$device[device_id]] as $index => $cmp) {
  if(is_array($cmp)) {
    if(mysql_result(mysql_query("SELECT count(cmp_id) FROM `cmpMemPool` WHERE `Index` = '$index' AND `device_id` = '".$device['device_id']."'"),0) == '0') {
      $query = "INSERT INTO cmpMemPool (`Index`,`cmpName`,`cmpAlternate`,`cmpValid`,`cmpUsed`,`cmpFree`,`cmpLargestFree`,`device_id`)
                                values ('$index', '".$cmp['ciscoMemoryPoolName']."', '".$cmp['ciscoMemoryPoolAlternate']."', 
                                        '".$cmp['ciscoMemoryPoolValid']."', '".$cmp['ciscoMemoryPoolUsed']."', '".$cmp['ciscoMemoryPoolFree']."', 
                                        '".$cmp['ciscoMemoryPoolLargestFree']."', '".$device['device_id']."')";
      mysql_query($query);
      echo("+");
    } else {

    }

    $valid_cmp[$index] = 1;
  }
}

$sql = "SELECT * FROM `cmpMemPool` WHERE `device_id`  = '".$device['device_id']."'";
$query = mysql_query($sql);

while ($test_ciscoMemoryPool = mysql_fetch_array($query)) {
  if(!$valid_cmp[$test_ciscoMemoryPool[Index]]) {
    echo("-");
    mysql_query("DELETE FROM `ciscoMemoryPool` WHERE ciscoMemoryPool_id = '" . $test['ciscoMemoryPool_id'] . "'");
  }
}

unset($valid_ciscoMemoryPool);
echo("\n");

?>
