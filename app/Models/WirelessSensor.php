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
     * Does this discovery item represent an actual item or did it fail validation
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->valid;
    }

    /**
     * Set the validation status of this discovery item
     *
     * @return bool
     */
    public function setValid($valid)
    {
        return $this->valid = $valid;
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
