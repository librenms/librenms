<?php

if (\LibreNMS\Config::get('enable_syslog')) {
    $syslog = dbFetchRows("SELECT *, DATE_FORMAT(timestamp, '" . \LibreNMS\Config::get('dateformat.mysql.compact') . "') AS date from syslog WHERE device_id = ? ORDER BY timestamp DESC LIMIT 20", [$device['device_id']]);
    if (count($syslog)) {
        echo '<div class="row">
          <div class="col-md-12">
            <div class="panel panel-default panel-condensed">
              <div class="panel-heading">';
        echo '<a href="device/device=' . $device['device_id'] . '/tab=logs/section=syslog/"><i class="fa fa-clone fa-lg icon-theme" aria-hidden="true"></i> <strong>Recent Syslog</strong></a>';
        echo '        </div>
              <table class="table table-hover table-condensed table-striped">';
        foreach ($syslog as $entry) {
            unset($syslog_output);
            include 'includes/html/print-syslog.inc.php';
            echo $syslog_output;
        }

        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}
