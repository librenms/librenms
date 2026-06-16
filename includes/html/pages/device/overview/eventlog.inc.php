<?php

use App\Models\Port;
use LibreNMS\Util\Time;

echo '  <div class="overview-panel tw:mb-5">
              <div class="tw:px-4 tw:py-2.5 tw:bg-neutral-100 tw:border-b tw:border-gray-300 tw:text-neutral-700 tw:dark:bg-dark-gray-200 tw:dark:border-zinc-800 tw:dark:text-dark-white-200">';
echo '<a href="' . route('device.eventlog', ['device' => $device['device_id']]) . '">';
echo '<i class="fa fa-bookmark fa-lg icon-theme" aria-hidden="true"></i> <strong>Recent Events</strong></a>';
echo '        </div>
              <div class="tw:flex tw:flex-col tw:bg-white tw:divide-y tw:divide-gray-300 tw:dark:bg-dark-gray-400 tw:dark:divide-zinc-800">';

$eventlog = dbFetchRows('SELECT * FROM `eventlog` WHERE `device_id` = ? ORDER BY `datetime` DESC LIMIT 0,10', [$device['device_id']]);
foreach ($eventlog as $entry) {
    $severity_colour = eventlog_severity($entry['severity']);
    $icon = '<span class="alert-status ' . $severity_colour . '"></span>';

    echo '<div class="tw:grid tw:items-center tw:gap-2.5 tw:px-2 tw:py-2 tw:hover:bg-neutral-100 tw:dark:hover:bg-dark-gray-300 tw:grid-cols-[auto_auto_100px_1fr]">';
    echo '<div>' . $icon . '</div>';
    echo '<div class="tw:whitespace-nowrap">' . Time::format($entry['datetime'], 'compact') . '</div>';
    echo '<div class="tw:truncate">';

    if ($entry['type'] == 'interface') {
        echo '<b>' . \LibreNMS\Util\Url::portLink(Port::find($entry['reference'])) . '</b>';
    }

    echo '</div><div>' . htmlspecialchars((string) $entry['message']) . '</div>';
    echo '</div>';
}

echo '</div>';
echo '</div>';
