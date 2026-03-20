<?php

/**
 * WirelessSensor.php
 *
 * Wireless Sensors
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Device;

use LibreNMS\Enum\WirelessSensorType;
use LibreNMS\Modules\Wireless;

class WirelessSensor
{
    protected $oids;
    protected string $rrd_type = 'GAUGE';

    /**
     * Sensor constructor. Create a new sensor to be discovered.
     *
     * @param  WirelessSensorType  $type  Class of this sensor, must be a supported class
     * @param  int  $device_id  the device_id of the device that owns this sensor
     * @param  array|string  $oids  an array or single oid that contains the data for this sensor
     * @param  string  $subtype  the type of sensor an additional identifier to separate out sensors of the same class, generally this is the os name
     * @param  int|string  $index  the index of this sensor, must be stable, generally the index of the oid
     * @param  string  $description  A user visible description of this sensor, may be truncated in some places (like graphs)
     * @param  int|float|null  $current  The current value of this sensor, will seed the db and may be used to guess limits
     * @param  int  $multiplier  a number to multiply the value(s) by
     * @param  int  $divisor  a number to divide the value(s) by
     * @param  string  $aggregator  an operation to combine multiple numbers. Supported: sum, avg
     * @param  int  $access_point_id  The id of the AP in the access_points sensor this belongs to (generally used for controllers)
     * @param  int|float  $high_limit  Alerting: Maximum value
     * @param  int|float  $low_limit  Alerting: Minimum value
     * @param  int|float  $high_warn  Alerting: High warning value
     * @param  int|float  $low_warn  Alerting: Low warning value
     * @param  string|int|null  $entPhysicalIndex  The entPhysicalIndex this sensor is associated, often a port
     * @param  string|null  $entPhysicalMeasured  the table to look for the entPhysicalIndex, for example 'ports' (maybe unused)
     */
    public function __construct(
        protected WirelessSensorType $type,
        protected $device_id,
        $oids,
        protected $subtype,
        protected $index,
        protected $description,
        protected $current = null,
        protected $multiplier = 1,
        protected $divisor = 1,
        protected $aggregator = 'sum',
        protected $access_point_id = null,
        protected $high_limit = null,
        protected $low_limit = null,
        protected $high_warn = null,
        protected $low_warn = null,
        protected $entPhysicalIndex = null,
        protected $entPhysicalMeasured = null
    ) {
        $this->oids = (array) $oids;
        $this->rrd_type = $this->type === WirelessSensorType::Errors ? 'COUNTER' : 'GAUGE';
    }

    public function toModel(): \App\Models\WirelessSensor
    {
        return new \App\Models\WirelessSensor([
            'sensor_class' => $this->type,
            'sensor_type' => $this->subtype,
            'sensor_index' => $this->index,
            'sensor_descr' => $this->description,
            'sensor_current' => $this->current,
            'sensor_multiplier' => $this->multiplier,
            'sensor_divisor' => $this->divisor,
            'sensor_aggregator' => $this->aggregator,
            'sensor_limit' => $this->high_limit,
            'sensor_limit_warn' => $this->high_warn,
            'sensor_limit_low' => $this->low_limit,
            'sensor_limit_low_warn' => $this->low_warn,
            'entPhysicalIndex' => $this->entPhysicalIndex,
            'entPhysicalMeasured' => $this->entPhysicalMeasured,
            'sensor_oids' => $this->oids,
            'access_point_id' => $this->access_point_id,
            'rrd_type' => $this->rrd_type,
        ]);
    }

    /**
     * Convert a WiFi channel to a Frequency in MHz
     * Legacy compat, use \LibreNMS\Modules\Wireless::channelToFrequency()
     *
     * @param  int  $channel
     * @return int
     */
    public static function channelToFrequency($channel): int
    {
        return Wireless::channelToFrequency((int) $channel);
    }
}
