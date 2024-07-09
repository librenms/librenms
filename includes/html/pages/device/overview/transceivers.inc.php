<?php
/**
 * transceivers.inc.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2024 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
if (DeviceCache::get($device['device_id'])->transceivers->isNotEmpty()) {
    DeviceCache::get($device['device_id'])->transceivers->load(['port', 'metrics']);
    echo view('device.overview.transceivers', [
        'transceivers' => DeviceCache::get($device['device_id'])->transceivers,
        'transceivers_link' => route('device', ['device' => $device['device_id'], 'tab' => 'ports', 'vars' => 'transceivers']),
        'filterMetrics' => fn ($metrics) => $metrics->filter(fn ($m) => in_array($m->type, ['power-rx', 'temperature'])),
    ]);
}
