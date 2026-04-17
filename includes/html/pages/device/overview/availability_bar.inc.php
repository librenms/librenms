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
 * @copyright  2026 Palerm0 <Palerm0@outlook.com>
 * @author     Palerm0 <Palerm0@outlook.com>
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

// Thresholds (configurable via config.php)
$threshold_good = \App\Facades\LibrenmsConfig::get('availability_bar.threshold_good', 99);
$threshold_medium = \App\Facades\LibrenmsConfig::get('availability_bar.threshold_medium', 95);

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
    } elseif ($availability >= $threshold_good) {
        $color = '#2ecc71';
    } elseif ($availability >= $threshold_medium) {
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
echo '<div class="panel-body tw:px-4 tw:py-2.5">';
echo '<div class="tw:flex tw:gap-0.5 tw:items-center">';

foreach ($day_data as $day) {
    $tip = '<div class="tw:font-bold tw:mb-1 tw:text-gray-800">' . $day['date'] . '</div>';

    if ($day['outages'] === ['no_data']) {
        $tip .= '<div class="tw:text-gray-400">— No data</div>';
    } elseif (empty($day['outages'])) {
        $tip .= '<div class="tw:text-green-600">&#10003; No outage</div>';
    } else {
        foreach ($day['outages'] as $line) {
            $tip .= '<div class="tw:text-red-500">&#10007; ' . $line . '</div>';
        }
    }

    echo '<div x-data="{ open:false, x:0, y:0, place(){ const r=this.$el.getBoundingClientRect(); this.x=r.left+r.width/2; this.y=r.top; this.$nextTick(()=>{ const w=this.$refs.tip?.offsetWidth||0; const pad=8; this.x=Math.max(pad+w/2, Math.min(window.innerWidth-pad-w/2, this.x)); }); } }" @mouseenter="open=true; place()" @mouseleave="open=false" @scroll.window="open && place()" @resize.window="open && place()"';
    echo ' class="tw:flex-1 tw:h-[34px] tw:rounded-sm tw:cursor-pointer tw:relative" style="background:' . $day['color'] . ';">';
    echo '<div x-ref="tip" x-show="open" x-cloak :style="`left:${x}px; top:${y - 8}px; transform: translate(-50%, -100%);`"';
    echo ' class="tw:fixed tw:bg-white tw:border tw:border-gray-300 tw:rounded tw:min-w-[280px] tw:px-8 tw:py-5 tw:text-xl tw:font-medium tw:whitespace-nowrap tw:z-[9999] tw:shadow-md tw:pointer-events-none">';
    echo $tip;
    echo '</div>';
    echo '</div>';
}

echo '</div>';
echo '<div class="tw:flex tw:justify-between tw:text-[11px] tw:text-gray-400 tw:mt-1">';
echo '<span>90 days ago</span>';
echo '<span><strong>' . $total_avail . '% uptime</strong></span>';
echo '<span>Today</span>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
echo '</div>';
