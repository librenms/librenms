<?php
/**
 * WirelessSensor.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use LibreNMS\Interfaces\Discovery\DiscoveryItem;
use LibreNMS\Interfaces\Discovery\DiscoveryModule;
use LibreNMS\OS;
use LibreNMS\Util\DiscoveryModelObserver;

class WirelessSensor extends BaseModel implements DiscoveryModule, DiscoveryItem //, PollerModule,
{
    protected $primaryKey = 'wireless_sensor_id';
    protected $fillable = ['type', 'device_id', 'oids', 'subtype', 'index', 'description', 'value', 'multiplier', 'divisor', 'aggregator', 'access_point_id', 'alert_high', 'alert_low', 'warn_high', 'warn_low'];

    private $valid = true;

    public static function discover(
        $type,
        $device_id,
        $oids,
        $subtype,
        $index,
        $description,
        $value = null,
        $multiplier = 1,
        $divisor = 1,
        $aggregator = 'sum',
        $access_point_id = null,
        $alert_high = null,
        $warn_high = null,
        $alert_low = null,
        $warn_low = null
    )
    {
        $sensor_array = get_defined_vars();

        $sensor = WirelessSensor::where('device_id', $device_id)
            ->firstOrNew(compact(['type', 'subtype', 'index']), $sensor_array);

        if ($sensor->wireless_sensor_id) {
            $ignored = ['value'];
            if ($sensor->custom_thresholds) {
                $ignored[] = 'alert_high';
                $ignored[] = 'alert_low';
                $ignored[] = 'warn_high';
                $ignored[] = 'warn_low';
            }

            $sensor->fill(array_diff_key($sensor_array, array_flip($ignored)));
        }

        if (is_null($value)) {
            // fetch data
        }

        if (!is_numeric($sensor->value)) {
            $sensor->valid = false;
        }

        return $sensor;
    }

    // ---- Helper Functions ----

    public function classDescr()
    {
        return collect(collect(\LibreNMS\Device\WirelessSensor::getTypes())
            ->get($this->sensor_class, []))
            ->get('short', ucwords(str_replace('_', ' ', $this->sensor_class)));
    }

    public function icon()
    {
        return collect(collect(\LibreNMS\Device\WirelessSensor::getTypes())
            ->get($this->sensor_class, []))
            ->get('icon', 'signal');
    }

    // ---- Query Scopes ----

    public function scopeHasAccess($query, User $user)
    {
        return $this->hasDeviceAccess($query, $user);
    }

    // ---- Define Relationships ----

    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id');
    }

    public static function runDiscovery(OS $os)
    {
        // check yaml first
//        $processors = self::processYaml($os);

        // output update status
        WirelessSensor::observe(new DiscoveryModelObserver());

        foreach (self::getTypes() as $type => $descr) {
            echo "$type: ";

            // save valid sensors, and collect ids
            $valid_ids = static::discoverType($os, $type)
                ->filter->isValid()
                ->each->save()
                ->pluck(['wireless_sensor_id']);

            // remove invalid sensors (mass delete will not trigger Eloquent deleted event)
            $deleted = WirelessSensor::where('device_id', $os->getDeviceId())
                ->where('type', $type)
                ->whereNotIn('wireless_sensor_id', $valid_ids)->delete();
            echo str_repeat('-', $deleted);

            echo PHP_EOL;
        }
    }

    protected static function discoverType(OS $os, $type)
    {
        $typeInterface = static::getDiscoveryInterface($type);
        if (!interface_exists($typeInterface)) {
            echo "ERROR: Discovery Interface doesn't exist! $typeInterface\n";
        }

        $have_discovery = $os instanceof $typeInterface;
        if ($have_discovery) {
            $function = static::getDiscoveryMethod($type);
            $sensors = $os->$function();

            if (is_array($sensors)) {
                return collect($sensors);
            } else {
                c_echo("%RERROR:%n $function did not return an array! Skipping discovery.");
            }
        }

        return collect();  // delete non existent sensors
    }


    /**
     * Return a list of valid types with metadata about each type
     * $class => array(
     *  'short' - short text for this class
     *  'long'  - long text for this class
     *  'unit'  - units used by this class 'dBm' for example
     *  'icon'  - font awesome icon used by this class
     * )
     * @param bool $valid filter this list by valid types in the database
     * @param int $device_id when filtering, only return types valid for this device_id
     * @return array
     */
    public static function getTypes($valid = false, $device_id = null)
    {
        // Add new types here
        // FIXME I'm really bad with icons, someone please help!
        static $types = [
            'ap-count' => [
                'short' => 'APs',
                'long' => 'AP Count',
                'unit' => '',
                'icon' => 'wifi',
            ],
            'clients' => [
                'short' => 'Clients',
                'long' => 'Client Count',
                'unit' => '',
                'icon' => 'tablet',
            ],
            'quality' => [
                'short' => 'Quality',
                'long' => 'Quality',
                'unit' => '%',
                'icon' => 'feed',
            ],
            'capacity' => [
                'short' => 'Capacity',
                'long' => 'Capacity',
                'unit' => '%',
                'icon' => 'feed',
            ],
            'utilization' => [
                'short' => 'Utilization',
                'long' => 'utilization',
                'unit' => '%',
                'icon' => 'percent',
            ],
            'rate' => [
                'short' => 'Rate',
                'long' => 'TX/RX Rate',
                'unit' => 'bps',
                'icon' => 'tachometer',
            ],
            'ccq' => [
                'short' => 'CCQ',
                'long' => 'Client Connection Quality',
                'unit' => '%',
                'icon' => 'wifi',
            ],
            'snr' => [
                'short' => 'SNR',
                'long' => 'Signal-to-Noise Ratio',
                'unit' => 'dB',
                'icon' => 'signal',
            ],
            'ssr' => [
                'short' => 'SSR',
                'long' => 'Signal Strength Ratio',
                'unit' => 'dB',
                'icon' => 'signal',
            ],
            'mse' => [
                'short' => 'MSE',
                'long' => 'Mean Square Error',
                'unit' => 'dB',
                'icon' => 'signal',
            ],
            'rssi' => [
                'short' => 'RSSI',
                'long' => 'Received Signal Strength Indicator',
                'unit' => 'dBm',
                'icon' => 'signal',
            ],
            'power' => [
                'short' => 'Power/Signal',
                'long' => 'TX/RX Power or Signal',
                'unit' => 'dBm',
                'icon' => 'bolt',
            ],
            'noise-floor' => [
                'short' => 'Noise Floor',
                'long' => 'Noise Floor',
                'unit' => 'dBm/Hz',
                'icon' => 'signal',
            ],
            'errors' => [
                'short' => 'Errors',
                'long' => 'Errors',
                'unit' => '',
                'icon' => 'exclamation-triangle',
                'type' => 'counter',
            ],
            'error-ratio' => [
                'short' => 'Error Ratio',
                'long' => 'Bit/Packet Error Ratio',
                'unit' => '%',
                'icon' => 'exclamation-triangle',
            ],
            'error-rate' => [
                'short' => 'BER',
                'long' => 'Bit Error Rate',
                'unit' => 'bps',
                'icon' => 'exclamation-triangle',
            ],
            'frequency' => [
                'short' => 'Frequency',
                'long' => 'Frequency',
                'unit' => 'MHz',
                'icon' => 'line-chart',
            ],
            'distance' => [
                'short' => 'Distance',
                'long' => 'Distance',
                'unit' => 'km',
                'icon' => 'space-shuttle',
            ],
        ];

        if ($valid) {
            $query = WirelessSensor::select('sensor_class');

            if (isset($device_id)) {
                $query->where('device_id', $device_id);
            }

            return collect($types)
                ->intersectKey($query->groupBy('sensor_class')
                    ->pluck('sensor_class')->flip());
        }

        return collect($types);
    }

    protected static function getDiscoveryInterface($type)
    {
        return str_to_class($type, 'LibreNMS\\Interfaces\\Discovery\\Sensors\\Wireless') . 'Discovery';
    }

    protected static function getDiscoveryMethod($type)
    {
        return 'discoverWireless' . str_to_class($type);
    }

    protected static function getPollingInterface($type)
    {
        return str_to_class($type, 'LibreNMS\\Interfaces\\Polling\\Sensors\\Wireless') . 'Polling';
    }

    protected static function getPollingMethod($type)
    {
        return 'pollWireless' . str_to_class($type);
    }

    /**
     * Does this item represent an actual item or did it fail validation
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Generate an instance of this class from yaml data.
     * The data is processed and any snmp data is filled in
     *
     * @param OS $os
     * @param int $index the index of the current entry
     * @param array $data
     * @return static
     */
    public static function fromYaml(OS $os, $index, array $data)
    {
        // TODO: Implement fromYaml() method.
    }
}
