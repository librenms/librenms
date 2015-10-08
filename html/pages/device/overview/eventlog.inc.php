<?php

echo '<div class="container-fluid">';
echo '  <div class="row">
          <div class="col-md-12">
            <div class="panel panel-default panel-condensed">
              <div class="panel-heading">';
echo '<a href="device/device='.$device['device_id'].'/tab=logs/section=eventlog/">';
echo "<img src='images/16/report.png'> <strong>Recent Events</strong></a>";
echo '        </div>
              <table class="table table-hover table-condensed table-striped">';

$eventlog = dbFetchRows("SELECT *,DATE_FORMAT(datetime, '".$config['dateformat']['mysql']['compact']."') as humandate FROM `eventlog` WHERE `host` = ? ORDER BY `datetime` DESC LIMIT 0,10", array($device['device_id']));
foreach ($eventlog as $entry) {
    include 'includes/print-event-short.inc.php';
}

echo '</table>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
