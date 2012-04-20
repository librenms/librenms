<?php

foreach (dbFetchRows("SELECT * FROM mempools WHERE device_id = ?", array($device['device_id'])) as $mempool)
{
  echo("Mempool ". $mempool['mempool_descr'] . ": ");

  $mempool_rrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("mempoolX-" . $mempool['mempool_type'] . "-" . $mempool['mempool_index'] . ".rrd");

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

  if (!is_file($mempool_rrd))
  {
   rrdtool_create($mempool_rrd, "--step 300 DS:used:GAUGE:600:0:U DS:free:GAUGE:600:0:U DS:size:GAUGE:600:0:U DS:perc:GAUGE:600:0:100 ".$config['rrd_rra']);
  }
  rrdtool_update($mempool_rrd,"N:".$mempool['used'].":".$mempool['free'].":".$mempool['total'].":".$percent);

  dbUpdate(array('mempool_used' => $mempool['used'], 'mempool_perc' => $percent, 'mempool_free' => $mempool['free'],
                 'mempool_total' => $mempool['total'], 'mempool_largestfree' => $mempool['largestfree'], 'mempool_lowestfree' => $mempool['lowestfree']),
                 'mempools', '`mempool_id` = ?', array($mempool['mempool_id']));

  echo("\n");
}

unset($mempool_cache);

?>
