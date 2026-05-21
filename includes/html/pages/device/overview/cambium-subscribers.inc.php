<?php

/**
 * cambium-subscribers.inc.php
 *
 * Device overview panel: list connected SMs for a Cambium ePMP AP
 * with the canonical link-health badges (downlink RSSI and downlink
 * MCS) plus link distance.  Per-SM detail and history live on the
 * device's wireless tab.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 */

use Illuminate\Support\Str;

if (($device['os'] ?? null) !== 'epmp') {
    return;
}

$subscriber_sensors = DeviceCache::getPrimary()->wirelessSensors()
    ->where('sensor_deleted', 0)
    ->whereIn('sensor_class', ['rssi', 'mcs', 'distance'])
    ->where('sensor_index', '!=', '0')
    ->orderBy('sensor_index')
    ->get();

if ($subscriber_sensors->isEmpty()) {
    return;
}

// The ePMP poller (PR #19462) writes sensor_descr in the form
// "<identity> <metric>", where <identity> is the subscriber label
// from Epmp::formatSubscriberLabel() and <metric> is one of a known
// fixed list. Strip the metric suffix to recover <identity>, then
// strip any trailing "(...)" or "[...]" groups to leave just the
// hostname-equivalent for the column display. All string-character
// operations; no regex.
$metric_suffixes = [
    ' UL RSSI', ' DL RSSI',
    ' UL SNR', ' DL SNR',
    ' UL MCS', ' DL MCS',
    ' Tx Quality', ' Tx Capacity', ' Distance',
];

$subscriberName = function (string $descr, string $fallback_index) use ($metric_suffixes): string {
    foreach ($metric_suffixes as $suffix) {
        if (str_ends_with($descr, $suffix)) {
            $descr = substr($descr, 0, -strlen($suffix));
            break;
        }
    }
    if (str_contains($descr, ' (')) {
        $descr = Str::before($descr, ' (');
    }
    if (str_contains($descr, ' [')) {
        $descr = Str::before($descr, ' [');
    }
    $descr = trim($descr);

    return $descr !== '' ? $descr : 'Subscriber ' . $fallback_index;
};

// ePMP per-subscriber sensor_type strings follow the pattern
// epmp-ap-{direction}[-{class}] (see PR #19462). RSSI and SNR end at
// the direction; MCS appends the class. Match either shape.
$findSensor = fn ($smSensors, string $class, ?string $direction = null) => $smSensors->first(function ($sensor) use ($class, $direction) {
    if ($sensor->sensor_class->value !== $class) {
        return false;
    }
    if ($direction === null) {
        return true;
    }

    return str_ends_with((string) $sensor->sensor_type, '-' . $direction)
        || str_contains((string) $sensor->sensor_type, '-' . $direction . '-');
});

echo view('device.overview.cambium-subscribers', [
    'sensors' => $subscriber_sensors,
    'subscribers_link' => route('device', ['device' => $device['device_id'], 'tab' => 'wireless']),
    'subscriberName' => $subscriberName,
    'findSensor' => $findSensor,
]);
