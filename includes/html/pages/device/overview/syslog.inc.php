<?php

if (\App\Facades\LibrenmsConfig::get('enable_syslog')) {
    $syslog = dbFetchRows("SELECT *, DATE_FORMAT(timestamp, '" . \App\Facades\LibrenmsConfig::get('dateformat.mysql.compact') . "') AS date from syslog WHERE device_id = ? ORDER BY timestamp DESC LIMIT 20", [$device['device_id']]);
    if (count($syslog)) {
        echo '<div class="overview-panel tw:mb-5">
              <div class="overview-panel-heading">';
        echo '<a href="' . route('device.syslog', ['device' => $device['device_id']]) . '"><i class="fa fa-clone fa-lg icon-theme" aria-hidden="true"></i> <strong>Recent Syslog</strong></a>';
        echo '        </div>
              <div class="overview-panel-body">';
        foreach ($syslog as $entry) {
            if (device_permitted($entry['device_id'])) {
                echo '<div class="overview-row tw:grid-cols-[auto_1fr]">
                    <div class="tw:whitespace-nowrap tw:italic">' . e($entry['date']) . '</div>
                    <div><strong>' . e($entry['program']) . '</strong>&nbsp;&nbsp;&nbsp;' . e($entry['msg']) . '</div>
                </div>';
            }
        }

        echo '</div>';
        echo '</div>';
    }
}
