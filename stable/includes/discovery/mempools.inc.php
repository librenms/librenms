<?php

echo("Memory : ");

### Include all discovery modules

$include_dir = "includes/discovery/mempools";
include("includes/include-dir.inc.php");

### Remove memory pools which weren't redetected here

$sql = "SELECT * FROM `mempools` WHERE `device_id`  = '".$device['device_id']."'";
$query = mysql_query($sql);

if ($debug) { print_r ($valid_mempool); }

while ($test_mempool = mysql_fetch_assoc($query))
{
  $mempool_index = $test_mempool['mempool_index'];
  $mempool_type = $test_mempool['mempool_type'];
  if ($debug) { echo($mempool_index . " -> " . $mempool_type . "\n"); }

  if (!$valid_mempool[$mempool_type][$mempool_index])
  {
    echo("-");
    mysql_query("DELETE FROM `mempools` WHERE mempool_id = '" . $test_mempool['mempool_id'] . "'");
  }

  unset($mempool_oid); unset($mempool_type);
}

unset($valid_mempool);
echo("\n");

?>
