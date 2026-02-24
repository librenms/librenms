<?php

echo '  <div class="row">
          <div class="col-md-12">
            <div class="panel panel-default panel-condensed">
              <div class="panel-heading">';
echo '<a href="' . route('device.eventlog', ['device' => $device['device_id']]) . '">';
echo '<i class="fa fa-bookmark fa-lg icon-theme" aria-hidden="true"></i> <strong>Recent Events</strong></a>';
echo '        </div>
              <table class="table table-hover table-condensed table-striped">';

$eventlog = dbFetchRows("SELECT *,DATE_FORMAT(IFNULL(CONVERT_TZ(datetime, @@global.time_zone, ?),datetime), '" . \App\Facades\LibrenmsConfig::get('dateformat.mysql.compact') . "') as humandate FROM `eventlog` WHERE `device_id` = ? ORDER BY `datetime` DESC LIMIT 0,10", [session('preferences.timezone'), $device['device_id']]);
foreach ($eventlog as $entry) {
    include 'includes/html/print-event-short.inc.php';
}

echo '</table>';
echo '</div>';
echo '</div>';
echo '</div>';
