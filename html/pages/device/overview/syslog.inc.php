<?php

if ($config['enable_syslog'])
{
  $syslog =  dbFetchRows("SELECT *, DATE_FORMAT(timestamp, '%Y-%m-%d %T') AS date from syslog WHERE device_id = ? ORDER BY timestamp DESC LIMIT 20", array($device['device_id']));
  if (count($syslog))
  {
echo('<div class="container-fluid">');
  echo('<div class="row">
          <div class="col-md-12">
            <div class="panel panel-default panel-condensed">
              <div class="panel-heading">');
    echo('<a href="device/device=' . $device['device_id'] . '/tab=logs/section=syslog/"><img src="images/16/printer.png" /> Recent Syslog</a>');
echo('        </div>
              <table class="table table-hover table-condensed table-striped">');
    foreach ($syslog as $entry) { include("includes/print-syslog.inc.php"); }
    echo("</table>");
    echo("</div>");
    echo("</div>");
    echo("</div>");
    echo("</div>");
  }
}

?>
