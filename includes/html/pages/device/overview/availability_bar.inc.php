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

use App\Facades\LibrenmsConfig;
use App\Models\DeviceOutage;
use Carbon\CarbonInterval;
use LibreNMS\Util\Time;

$device_obj = DeviceCache::getPrimary();
$device_id = $device_obj->device_id;
$days = 90;
$now = Time::now();
$start = $now->copy()->subDays($days);
$start_ts = $start->timestamp;
$now_ts = $now->timestamp;

$outages = DeviceOutage::where('device_id', $device_id)
    ->where(function ($q) use ($start_ts): void {
        $q->whereNull('up_again')
          ->orWhere('up_again', '>', $start_ts);
    })
    ->orderBy('going_down')
    ->toBase()
    ->get(['going_down', 'up_again']);

// Determine when the device was added
$inserted = $device_obj->inserted->timestamp ?? min(array_filter([
    $now_ts - $device_obj->uptime,
    $outages->first()?->going_down,
], fn ($v) => $v !== null));

// Thresholds
$threshold_ok = LibrenmsConfig::get('availablity.threshold_ok', 99.9);
$threshold_medium = LibrenmsConfig::get('availablity.threshold_warning', 95);

// Build per-day availability data
$day_data = [];
$current_day_start = $start->copy()->startOfDay();

for ($i = 0; $i < $days; $i++) {
    $day_start = $current_day_start->timestamp;
    $current_day_start->addDay();
    $day_end = $current_day_start->timestamp;
    $outage_seconds = 0;
    $outage_lines = [];

    foreach ($outages as $outage) {
        $down = max($outage->going_down, $day_start);
        $up = min($outage->up_again ?: $now_ts, $day_end);

        if ($up > $down) {
            $duration = $up - $down;
            $outage_seconds += $duration;
            $time_str = Time::format($outage->going_down, 'time');
            $duration_str = CarbonInterval::seconds($duration)->cascade()->forHumans(['short' => true, 'parts' => 2]);

            $outage_lines[] = "Outage at $time_str &bull; $duration_str";
        }
    }

    $time_period = min(86400, $now_ts - $day_start);
    if ($time_period <= 0) {
        $availability = 100;
    } else {
        $availability = max(0, min(100, 100 - ($outage_seconds / $time_period * 100)));
    }

    if ($day_start < $inserted) {
        $color = 'tw:bg-gray-300';
        $outage_lines = ['no_data'];
    } elseif ($availability >= $threshold_ok) {
        $color = 'tw:bg-green-400';
    } elseif ($availability >= $threshold_medium) {
        $color = 'tw:bg-orange-400';
    } else {
        $color = 'tw:bg-red-500';
    }
    $day_data[] = [
        'date' => Time::format($day_start, 'date'),
        'avail' => round($availability, 2),
        'color' => $color,
        'outages' => $outage_lines,
    ];
}

// Calculate total availability over the full period.
$total_outage = $outages->reduce(function ($carry, $outage) use ($start_ts, $now_ts) {
    $down = max($outage->going_down, $start_ts);
    $up = min($outage->up_again ?: $now_ts, $now_ts);

    return $carry + max(0, (int) $up - (int) $down);
}, 0);

echo <<<'HTML'
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default panel-condensed">
            <div class="panel-heading">
                <i class="fa fa-check-circle fa-lg icon-theme" aria-hidden="true"></i>
                <strong> Availability (90 days)</strong>
            </div>
            <div class="panel-body tw:px-4 tw:py-2.5">
                <div class="tw:flex tw:gap-px tw:items-center">
HTML;

foreach ($day_data as $day) {
    $tipLines = [];
    $tipLines[] = '<div class="tw:font-bold tw:mb-1 tw:text-gray-800">' . $day['date'] . '</div>';

    if ($day['outages'] === ['no_data']) {
        $tipLines[] = '<div class="tw:text-gray-400">— No data</div>';
    } elseif (empty($day['outages'])) {
        $tipLines[] = '<div class="tw:text-green-600">&#10003; No outage</div>';
    } else {
        foreach ($day['outages'] as $line) {
            $tipLines[] = '<div class="tw:text-red-500">&#10007; ' . $line . '</div>';
        }
    }
    $tip = implode('', $tipLines);

    echo <<<HTML
    <div x-data="{ open:false, x:0, y:0, place(){ const r=this.\$el.getBoundingClientRect(); this.x=r.left+r.width/2; this.y=r.top; this.\$nextTick(()=>{ const w=this.\$refs.tip?.offsetWidth||0; const pad=8; this.x=Math.max(pad+w/2, Math.min(window.innerWidth-pad-w/2, this.x)); }); } }"
         @mouseenter="open=true; place()" @mouseleave="open=false" @scroll.window="open && place()" @resize.window="open && place()"
         class="tw:flex-1 tw:h-12 tw:rounded-sm tw:cursor-pointer tw:relative {$day['color']}">
        <div x-ref="tip" x-show="open" x-cloak :style="`left:\${x}px; top:\${y - 8}px; transform: translate(-50%, -100%);`"
             class="tw:fixed tw:bg-white tw:border tw:border-gray-300 tw:rounded tw:min-w-70 tw:px-8 tw:py-5 tw:text-xl tw:font-medium tw:whitespace-nowrap tw:z-9999 tw:shadow-md tw:pointer-events-none">
            $tip
        </div>
    </div>
HTML;
}

$total_avail = round(
    max(0, min(100, 100 - ($total_outage / ($now_ts - $start_ts) * 100))),
    3
);

if ($total_avail >= $threshold_ok) {
    $total_color = '';
} elseif ($total_avail >= $threshold_medium) {
    $total_color = 'tw:text-orange-400';
} else {
    $total_color = 'tw:text-red-500';
}

echo <<<HTML
                </div>
                <div class="tw:flex tw:justify-between tw:text-[11px] tw:text-gray-400 tw:mt-1">
                    <span>90 days ago</span>
                    <span><strong class="$total_color">$total_avail% uptime</strong></span>
                    <span>Today</span>
                </div>
            </div>
        </div>
    </div>
</div>
HTML;
