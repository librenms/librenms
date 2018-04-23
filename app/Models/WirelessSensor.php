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
use LibreNMS\Modules\Wireless;
use LibreNMS\OS;

class WirelessSensor extends BaseModel implements DiscoveryItem
{
    protected $primaryKey = 'wireless_sensor_id';
    protected $fillable = ['type', 'device_id', 'oids', 'subtype', 'index', 'description', 'value', 'multiplier', 'divisor', 'aggregator', 'access_point_id', 'alert_high', 'alert_low', 'warn_high', 'warn_low'];
    protected $casts = ['oids' => 'array'];

    protected static $rrd_name = 'wireless-sensor';
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
        // ensure leading dots
        $oids = array_map(function($oid) {
            return '.' . ltrim($oid, '.');
        }, (array)$oids);

        // capture all input variables
        $sensor_array = get_defined_vars();


        $sensor = WirelessSensor::where('device_id', $device_id)
            ->firstOrNew(compact(['type', 'subtype', 'index']), $sensor_array);

        // if existing, update selected data
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
            $sensors = collect([$sensor]);

            $prefetch = Wireless::fetchSnmpData(Device::find($device_id), $sensors);
            $data = Wireless::processSensorData($sensors, $prefetch);

            $sensor->value = $data->first();
        }

        $sensor->valid = is_numeric($sensor->value);

        return $sensor;
    }

    // ---- Helper Functions ----

    public function inAlarm()
    {
        return (is_null($this->alert_high) ? false : $this->value >= $this->alert_high)
            || (is_null($this->alert_low) ? false : $this->value <= $this->alert_low);
    }

    public function classDescr()
    {
        return collect(Wireless::getTypes()
            ->get($this->type, []))
            ->get('short', ucwords(str_replace('_', ' ', $this->type)));
    }

    public function icon()
    {
        return collect(Wireless::getTypes()
            ->get($this->type, []))
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

    // ---- Discovery / Poller Module Functions ----


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

    public static function measurementName()
    {
        return 'wireless-sensor';
    }

    public function rrdName($hostname)
    {
        return rrd_name($hostname, [
            static::measurementName(),
            $this->type,
            $this->subtype,
            $this->index
        ]);
    }
}
