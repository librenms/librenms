<?php

foreach (dbFetchRows("SELECT * FROM mempools WHERE device_id = ?", array($device['device_id'])) as $mempool)
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

  dbUpdate(array('mempool_used' => $mempool['used'], 'mempool_perc' => $percent, 'mempool_free' => $mempool['free'],
                 'mempool_total' => $mempool['total'], 'mempool_largestfree' => $mempool['largestfree'], 'mempool_lowestfree' => $mempool['lowestfree']),
                 'mempools', '`mempool_id` = ?', array($mempool['mempool_id']));

  echo("\n");
}

unset($mempool_cache);

?>
