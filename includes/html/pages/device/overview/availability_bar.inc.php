<?php

/**
 * availability_bar.inc.php
 *
 * Displays a 90-day availability bar on the device overview page.
 * Each bar represents one day, colour-coded by availability percentage.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Your Name <your@email.address>
 * @author     Your Name <your@email.address>
 */
$device_obj = DeviceCache::getPrimary();
$device_id = $device_obj->device_id;
$now = time();
$days = 90;
$start = $now - ($days * 86400);

// Determine when the device was added
$inserted = $device_obj->inserted
    ? $device_obj->inserted->timestamp
    : $now;

$outages = \App\Models\DeviceOutage::where('device_id', $device_id)
    ->where(function ($q) use ($start): void {
        $q->where('going_down', '>=', $start)
          ->orWhere(function ($q2) use ($start): void {
              $q2->where('going_down', '<', $start)
                 ->where(function ($q3) use ($start): void {
                     $q3->whereNull('up_again')
                        ->orWhere('up_again', '>', $start);
                 });
          });
    })
    ->orderBy('going_down')
    ->get();

// Build per-day availability data
$day_data = [];
for ($i = 0; $i < $days; $i++) {
    $day_start = $start + ($i * 86400);
    $day_end = $day_start + 86400;
    $outage_seconds = 0;
    $outage_lines = [];

    foreach ($outages as $outage) {
        $down = max($outage->going_down, $day_start);
        $up = min($outage->up_again ?: $now, $day_end);

        if ($up > $down) {
            $duration = $up - $down;
            $outage_seconds += $duration;
            $hours = (int) floor($duration / 3600);
            $minutes = (int) floor(($duration % 3600) / 60);
            $time_str = date('H:i', $outage->going_down);
            $duration_str = $hours > 0
                ? "{$hours} hrs {$minutes} mins"
                : "{$minutes} mins";

            $outage_lines[] = 'Outage at ' . $time_str
                . ' &bull; ' . $duration_str;
        }
    }

    $availability = max(0, min(100, 100 - ($outage_seconds / 86400 * 100)));

    if ($day_start < $inserted) {
        $color = '#cccccc';
        $outage_lines = ['no_data'];
    } elseif ($availability >= 99) {
        $color = '#2ecc71';
    } elseif ($availability >= 95) {
        $color = '#f39c12';
    } else {
        $color = '#e74c3c';
    }
    $day_data[] = [
        'date' => date('d M Y', $day_start),
        'avail' => round($availability, 2),
        'color' => $color,
        'outages' => $outage_lines,
    ];
}

// Calculate total availability over the full period.
$total_outage = 0;
foreach ($outages as $outage) {
    $down = max($outage->going_down, $start);
    $up = min($outage->up_again ?: $now, $now);

    if ($up > $down) {
        $total_outage += ($up - $down);
    }
}

$total_avail = round(
    max(0, min(100, 100 - ($total_outage / ($days * 86400) * 100))),
    2
);

// Render the availability bar panel
echo '<div class="row">';
echo '<div class="col-md-12">';
echo '<div class="panel panel-default panel-condensed">';
echo '<div class="panel-heading">';
echo '<i class="fa fa-check-circle fa-lg icon-theme" aria-hidden="true">';
echo '</i><strong> Availability (90 days)</strong>';
echo '</div>';
echo '<div class="panel-body" style="padding:10px 15px;">';
echo '<style>';
echo '.avail-wrap{display:flex;gap:2px;align-items:center;}';
echo '.avail-bar{flex:1;height:34px;border-radius:2px;';
echo 'cursor:pointer;position:relative;}';
echo '.avail-tip{display:none;position:absolute;bottom:42px;';
echo 'left:50%;transform:translateX(-50%);background:#fff;';
echo 'border:1px solid #ccc;border-radius:4px;padding:7px 10px;';
echo 'font-size:12px;white-space:nowrap;z-index:9999;';
echo 'box-shadow:0 2px 6px rgba(0,0,0,0.15);pointer-events:none;}';
echo '.avail-bar:hover .avail-tip{display:block;}';
echo '.avail-tip-date{font-weight:bold;margin-bottom:4px;color:#333;}';
echo '.avail-tip-ok{color:#27ae60;}';
echo '.avail-tip-err{color:#e74c3c;}';
echo '</style>';
echo '<div class="avail-wrap">';

foreach ($day_data as $day) {
    $tip = '<div class="avail-tip-date">' . $day['date'] . '</div>';

    if ($day['outages'] === ['no_data']) {
        $tip .= '<div class="avail-tip-ok" style="color:#999;">— No data</div>';
    } elseif (empty($day['outages'])) {
        $tip .= '<div class="avail-tip-ok">&#10003; No outage</div>';
    } else {
        foreach ($day['outages'] as $line) {
            $tip .= '<div class="avail-tip-err">&#10007; ' . $line . '</div>';
        }
    }
    echo '<div class="avail-bar" style="background:' . $day['color'] . ';">';
    echo '<div class="avail-tip">' . $tip . '</div>';
    echo '</div>';
}

echo '</div>';
echo '<div style="display:flex;justify-content:space-between;';
echo 'font-size:11px;color:#888;margin-top:4px;">';
echo '<span>90 days ago</span>';
echo '<span><strong>' . $total_avail . '% uptime</strong></span>';
echo '<span>Today</span>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
