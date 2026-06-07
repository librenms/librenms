<?php

if (\App\Facades\LibrenmsConfig::get('enable_syslog')) {
    $syslog = dbFetchRows("SELECT *, DATE_FORMAT(timestamp, '" . \App\Facades\LibrenmsConfig::get('dateformat.mysql.compact') . "') AS date from syslog WHERE device_id = ? ORDER BY timestamp DESC LIMIT 20", [$device['device_id']]);
    if (count($syslog)) {
        echo '<div class="overview-panel tw:mb-5">
              <div class="tw:px-4 tw:py-2.5 tw:bg-[#f5f5f5] tw:border-b tw:border-gray-300 tw:text-[#333] tw:dark:bg-dark-gray-200 tw:dark:border-[#1c1e22] tw:dark:text-dark-white-200">';
        echo '<a href="' . route('device.syslog', ['device' => $device['device_id']]) . '"><i class="fa fa-clone fa-lg icon-theme" aria-hidden="true"></i> <strong>Recent Syslog</strong></a>';
        echo '        </div>
              <div class="tw:flex tw:flex-col tw:bg-white tw:divide-y tw:divide-gray-300 tw:dark:bg-dark-gray-400 tw:dark:divide-[#1c1e22]">';
        foreach ($syslog as $entry) {
            if (device_permitted($entry['device_id'])) {
                echo '<div class="tw:grid tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-[#f5f5f5] tw:dark:hover:bg-dark-gray-300 tw:grid-cols-[auto_1fr]">
                    <div class="tw:whitespace-nowrap tw:italic">' . e($entry['date']) . '</div>
                    <div><strong>' . e($entry['program']) . '</strong>&nbsp;&nbsp;&nbsp;' . e($entry['msg']) . '</div>
                </div>';
            }
        }

        echo '</div>';
        echo '</div>';
    }
}
