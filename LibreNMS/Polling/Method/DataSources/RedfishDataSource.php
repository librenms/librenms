<?php

/**
 * RedfishDataSource.php
 *
 * The sensors of a Redfish service (BMC)
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
 * @copyright  2026 Kevin Zink
 * @author     Kevin Zink <kevin.zink@mpi-hd.mpg.de>
 */

namespace LibreNMS\Polling\Method\DataSources;

use App\Models\Sensor;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\SensorsDiscovery;
use LibreNMS\Interfaces\Polling\SensorsPolling;
use LibreNMS\Polling\Method\ProbeResult;

/**
 * @phpstan-type Reading array{name: string, class: string, value: float|null, warn_low: float|null, warn_high: float|null, crit_low: float|null, crit_high: float|null, chassis: string, source: string}
 */
final readonly class RedfishDataSource implements SensorsDiscovery, SensorsPolling
{
    public function __construct(
        private ProbeResult $result,
    ) {
    }

    /**
     * The check crawled the sensors of all chassis, discovery maps the readings it fetched.
     *
     * @return Collection<int, Sensor>
     */
    public function discoverSensors(): Collection
    {
        /** @var list<Reading> $readings */
        $readings = $this->result->stat('readings', []);
        $sensors = new Collection;
        $index = 0;

        foreach ($readings as $reading) {
            $sensors->push(new Sensor([
                'poller_type' => 'redfish',
                'sensor_class' => $reading['class'],
                'sensor_oid' => $reading['source'],
                'sensor_index' => $index++,
                'sensor_type' => 'redfish',
                'sensor_descr' => $reading['name'],
                'sensor_limit' => $reading['crit_high'],
                'sensor_limit_warn' => $reading['warn_high'],
                'sensor_limit_low' => $reading['crit_low'],
                'sensor_limit_low_warn' => $reading['warn_low'],
                'sensor_current' => $reading['value'],
                'rrd_type' => 'GAUGE',
            ]));
        }

        return $sensors;
    }

    /**
     * Each sensor is read from the source it was discovered from, sensors the crawl did not reach are left out.
     *
     * @param  EloquentCollection<int, Sensor>  $sensors
     * @return array<int, float>
     */
    public function pollSensors(EloquentCollection $sensors): array
    {
        /** @var list<Reading> $readings */
        $readings = $this->result->stat('readings', []);
        $bySource = [];
        foreach ($readings as $reading) {
            if ($reading['value'] !== null) {
                $bySource[$reading['source']] = $reading['value'];
            }
        }

        $values = [];
        foreach ($sensors as $sensor) {
            if (($value = $bySource[$sensor->sensor_oid] ?? null) !== null) {
                $values[$sensor->sensor_id] = $value;
            }
        }

        return $values;
    }
}
