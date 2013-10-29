<?php

// Generate some statistics to send along with the version request.

$stats['ports']        = dbFetchCell("SELECT count(*) FROM ports");
$stats['devices']      = dbFetchCell("SELECT count(*) FROM devices");
$stats['sensors']      = dbFetchCell("SELECT count(*) FROM sensors");
$stats['services']     = dbFetchCell("SELECT count(*) FROM services");
$stats['applications'] = dbFetchCell("SELECT count(*) FROM applications");
$stats['bgp']          = dbFetchCell("SELECT count(*) FROM bgpPeers");

foreach (dbFetch("SELECT COUNT(*) AS count,os from devices group by `os`") as $dt_data)
{
  $stats['devicetypes'][$dt_data['os']] = $dt_data['count'];
}

$stat_serial = base64_encode(serialize($stats));

#$url = "http://www.observium.org/latest.php?i=".$stats['ports']."&d=".$stats['devices']."&stats=".$stat_serial."&v=".$config['version'];
#$dataHandle = fopen($url, r);

if ($dataHandle)
{
  while (!feof($dataHandle))
  {
    $data.= fread($dataHandle, 4096);
  }
  if ($data)
  {
    list($omnipotence, $year, $month, $revision) = explode(".", $data);
    list($cur, $tag) = explode("-", $config['version']);
    list($cur_omnipotence, $cur_year, $cur_month, $cur_revision) = explode(".", $cur);

    if ($argv[1] == "--cron" || isset($options['q']))
    {
      $fd = fopen($config['log_file'],'a');
      fputs($fd,$string . "\n");
      fclose($fd);

      shell_exec("echo $omnipotence.$year.$month.$month > ".$config['rrd_dir']."/version.txt ");
    } else {
      if ($cur != $data)
      {
        echo("Current Revision : $cur_revision\n");

        if ($omnipotence > $cur_revision)
        {
          echo("New Revision   : $omnipotence\n");
        }

#        if ($omnipotence > $cur_omnipotence)
#        {
#          echo("New version     : $omnipotence.$year.$month.$revision\n");
#        } elseif ($year > $cur_year) {
#          echo("New version     : $omnipotence.$year.$month.$revision\n");
#        } elseif ($month > $cur_month) {
#          echo("New version     : $omnipotence.$year.$month.$revision\n");
#        } elseif ($revision > $cur_revision) {
#          echo("New release     : $omnipotence.$year.$month.$revision\n");
#        }
      }
    }
  }

  fclose($dataHandle);
}

?>
