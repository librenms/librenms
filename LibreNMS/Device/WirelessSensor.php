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

use LibreNMS\Modules\Wireless;

class WirelessSensor
{
    protected $type;
    protected $device_id;
    protected $oids;
    protected $subtype;
    protected $index;
    protected $description;
    protected $current;
    protected $multiplier;
    protected $divisor;
    protected $aggregator;
    protected $high_limit;
    protected $low_limit;
    protected $high_warn;
    protected $low_warn;
    protected $entPhysicalIndex;
    protected $entPhysicalMeasured;
    protected string $rrd_type = 'GAUGE';
    protected $access_point_ip;

    /**
     * Sensor constructor. Create a new sensor to be discovered.
     *
     * @param  string  $type  Class of this sensor, must be a supported class
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
     * @param  int|float  $entPhysicalIndex  The entPhysicalIndex this sensor is associated, often a port
     * @param  int|float  $entPhysicalMeasured  the table to look for the entPhysicalIndex, for example 'ports' (maybe unused)
     */
    public function __construct(
        $type,
        $device_id,
        $oids,
        $subtype,
        $index,
        $description,
        $current = null,
        $multiplier = 1,
        $divisor = 1,
        $aggregator = 'sum',
        $access_point_id = null,
        $high_limit = null,
        $low_limit = null,
        $high_warn = null,
        $low_warn = null,
        $entPhysicalIndex = null,
        $entPhysicalMeasured = null
    ) {
        $this->access_point_ip = $access_point_id;
        $this->type = $type;
        $this->device_id = $device_id;
        $this->oids = (array) $oids;
        $this->subtype = $subtype;
        $this->index = $index;
        $this->description = $description;
        $this->current = $current;
        $this->multiplier = $multiplier;
        $this->divisor = $divisor;
        $this->aggregator = $aggregator;
        $this->entPhysicalIndex = $entPhysicalIndex;
        $this->entPhysicalMeasured = $entPhysicalMeasured;
        $this->high_limit = $high_limit;
        $this->low_limit = $low_limit;
        $this->high_warn = $high_warn;
        $this->low_warn = $low_warn;
        $this->rrd_type = $this->type == 'errors' ? 'COUNTER' : 'GAUGE';
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
            'access_point_ip' => $this->access_point_ip,
            'rrd_type' => $this->rrd_type,
        ]);
    }

    /**
     * Return a list of valid types with metadata about each type
     * $class => array(
     *  'short' - short text for this class
     *  'long'  - long text for this class
     *  'unit'  - units used by this class 'dBm' for example
     *  'icon'  - font awesome icon used by this class
     * )
     *
     * @param  bool  $valid  filter this list by valid types in the database
     * @param  int  $device_id  when filtering, only return types valid for this device_id
     * @return array
     */
    public static function getTypes($valid = false, $device_id = null): array
    {
        // Add new types here translations/descriptions/units in lang/<lang>/wireless.php
        // FIXME I'm really bad with icons, someone please help!
        static $types = [
            'ap-count' => [
                'icon' => 'wifi',
            ],
            'clients' => [
                'icon' => 'tablet',
            ],
            'quality' => [
                'icon' => 'feed',
            ],
            'capacity' => [
                'icon' => 'feed',
            ],
            'utilization' => [
                'icon' => 'percent',
            ],
            'rate' => [
                'icon' => 'tachometer',
            ],
            'ccq' => [
                'icon' => 'wifi',
            ],
            'snr' => [
                'icon' => 'signal',
            ],
            'sinr' => [
                'icon' => 'signal',
            ],
            'rsrp' => [
                'icon' => 'signal',
            ],
            'rsrq' => [
                'icon' => 'signal',
            ],
            'ssr' => [
                'icon' => 'signal',
            ],
            'mse' => [
                'icon' => 'signal',
            ],
            'xpi' => [
                'icon' => 'signal',
            ],
            'rssi' => [
                'icon' => 'signal',
            ],
            'power' => [
                'icon' => 'bolt',
            ],
            'noise-floor' => [
                'icon' => 'signal',
            ],
            'errors' => [
                'icon' => 'exclamation-triangle',
                'type' => 'counter',
            ],
            'error-ratio' => [
                'icon' => 'exclamation-triangle',
            ],
            'error-rate' => [
                'icon' => 'exclamation-triangle',
            ],
            'frequency' => [
                'icon' => 'line-chart',
            ],
            'distance' => [
                'icon' => 'space-shuttle',
            ],
            'cell' => [
                'icon' => 'line-chart',
            ],
            'channel' => [
                'icon' => 'line-chart',
            ],
        ];

        if ($valid) {
            $sensors = \App\Models\WirelessSensor::query()
                ->when($device_id, fn ($q) => $q->where('device_id', $device_id))
                ->groupBy('sensor_class')
                ->pluck('sensor_class');

            return array_intersect_key($types, $sensors->flip()->all());
        }

        return $types;
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
