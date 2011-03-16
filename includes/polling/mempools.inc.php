<?php

$query = "SELECT * FROM mempools WHERE device_id = '" . $device['device_id'] . "'";
$mempool_data = mysql_query($query);
while ($mempool = mysql_fetch_array($mempool_data))
{
  echo("Mempool ". $mempool['mempool_descr'] . ": ");

  $mempoolrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("mempool-" . $mempool['mempool_type'] . "-" . $mempool['mempool_index'] . ".rrd");

  if (!is_file($mempoolrrd))
  {
   rrdtool_create($mempoolrrd, "--step 300 \
     DS:used:GAUGE:600:-273:100000000000 \
     DS:free:GAUGE:600:-273:100000000000 \
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

  $file = $config['install_dir']."/includes/polling/mempools/".$mempool['mempool_type'].".inc.php";
  if (is_file($file))
  {
    include($file);
  } else {
    ### Do we need a generic mempool poller?
  }

  if ($mempool['total'])
  {
    $percent = round($mempool['used'] / $mempool['total'] * 100, 2);
  }
  else
  {
    $percent = 0;
  }

  echo($percent."% ");

  rrdtool_update($mempoolrrd,"N:".$mempool['used'].":".$mempool['free']);

  $update_query  = "UPDATE `mempools` SET `mempool_used` = '".$mempool['used']."'";
  $update_query .= ", `mempool_free` = '".$mempool['free']."'";
  $update_query .= ", `mempool_total` = '".$mempool['total']."'";
  $update_query .= ", `mempool_largestfree` = '".$mempool['largestfree']."'";
  $update_query .= ", `mempool_lowestfree` = '".$mempool['lowestfree']."'";
  $update_query .= " WHERE `mempool_id` = '".$mempool['mempool_id']."'";

  mysql_query($update_query);
  if ($debug) { echo($update_query); }

  echo("\n");
}

unset($mempool_cache);

?>