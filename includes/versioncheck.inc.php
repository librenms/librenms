<?php

### Generate some statistics to send along with the version request.

$stats['ports']        = mysql_result(mysql_query("SELECT count(*) FROM ports"),0);
$stats['devices']      = mysql_result(mysql_query("SELECT count(*) FROM devices"),0);
$stats['sensors']      = mysql_result(mysql_query("SELECT count(*) FROM sensors"),0);
$stats['services']     = mysql_result(mysql_query("SELECT count(*) FROM services"),0);
$stats['applications'] = mysql_result(mysql_query("SELECT count(*) FROM applications"),0);
$stats['bgp']          = mysql_result(mysql_query("SELECT count(*) FROM bgpPeers"),0);

$dt_query      = mysql_query("SELECT `os` FROM `devices` GROUP BY `os`");
while($dt_data = mysql_fetch_assoc($dt_query))
{
  $stats['devicetypes'][$dt_data[os]] = mysql_result(mysql_query("SELECT COUNT(*) FROM `devices` WHERE `os` = '".$dt_data['os']."'"),0);
}

$stats = serialize($stats[$dt_data[os]]);

$dataHandle = fopen("http://www.observium.org/latest.php?i=".$stats['ports']."&d=".$stats['devices']."&stats=".$stats."&v=".$config['version'], r);

if($dataHandle)
{
        while (!feof($dataHandle))
        {
                $data.= fread($dataHandle, 4096);
        }
        if($data)
        {
                list($omnipotence, $year, $month, $revision) = explode(".", $data);
                list($cur, $tag) = explode("-", $config['version']);
                list($cur_omnipotence, $cur_year, $cur_month, $cur_revision) = explode(".", $cur);

             if($argv[1] == "--cron") {

               $fd = fopen($config['log_file'],'a');
               fputs($fd,$string . "\n");
               fclose($fd);

                shell_exec("echo $omnipotence.$year.$month.$month > rrd/version.txt ");

             } else {

                if($cur != $data) {
                  echo("Current Version : $cur_omnipotence.$cur_year.$cur_month.$cur_revision \n");
                
                  if($omnipotence > $cur_omnipotence) {
                    echo("New version     : $omnipotence.$year.$month.$revision\n");
                  } elseif ($year > $cur_year) {
                    echo("New version     : $omnipotence.$year.$month.$revision\n");
                  } elseif ($month > $cur_month) {
                    echo("New version     : $omnipotence.$year.$month.$revision\n");
                  } elseif ($revision > $cur_revision) {
                    echo("New release     : $omnipotence.$year.$month.$revision\n");
                  }
                }
             }
        }
        fclose($dataHandle);
}

?>
