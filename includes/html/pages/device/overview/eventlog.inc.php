<?php

use App\Models\Port;
use LibreNMS\Util\Time;

echo '  <div class="overview-panel tw:mb-5">
              <div class="overview-panel-heading">';
echo '<a href="' . route('device.eventlog', ['device' => $device['device_id']]) . '">';
echo '<i class="fa fa-bookmark fa-lg icon-theme" aria-hidden="true"></i> <strong>Recent Events</strong></a>';
echo '        </div>
              <div class="overview-panel-body">';

$eventlog = dbFetchRows('SELECT * FROM `eventlog` WHERE `device_id` = ? ORDER BY `datetime` DESC LIMIT 0,10', [$device['device_id']]);
foreach ($eventlog as $entry) {
    $severity_colour = eventlog_severity($entry['severity']);
    $icon = '<span class="alert-status ' . $severity_colour . '"></span>';

    echo '<div class="overview-row tw:grid-cols-[auto_auto_100px_1fr]">';
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
