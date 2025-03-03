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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Illuminate\Support\Arr;
use LibreNMS\Interfaces\Models\Keyable;
use LibreNMS\Util\Number;

class WirelessSensor extends DeviceRelatedModel implements Keyable
{
    const CREATED_AT = null;
    const UPDATED_AT = 'lastupdate';
    protected $primaryKey = 'sensor_id';
    protected $fillable = [
        'sensor_class',
        'sensor_index',
        'sensor_type',
        'sensor_descr',
        'sensor_divisor',
        'sensor_multiplier',
        'sensor_aggregator',
        'sensor_current',
        'sensor_prev',
        'sensor_limit',
        'sensor_limit_warn',
        'sensor_limit_low',
        'sensor_limit_low_warn',
        'sensor_alert',
        'sensor_custom',
        'entPhysicalIndex',
        'entPhysicalIndex_measured',
        'lastupdate',
        'sensor_oids',
        'access_point_id',
        'rrd_type',
    ];
    protected $casts = ['sensor_oids' => 'array'];

    // ---- Helper Functions ----

    public function classDescr()
    {
        return __("wireless.$this->sensor_class.short");
    }

    public function icon()
    {
        return collect(collect(\LibreNMS\Device\WirelessSensor::getTypes())
            ->get($this->sensor_class, []))
            ->get('icon', 'signal');
    }

    public function getCompositeKey(): string
    {
        return "$this->sensor_class-$this->sensor_type-$this->sensor_index";
    }

    public function fillValue(array $values): self
    {
        if (empty($values)) {
            $this->sensor_current = null;

            return $this;
        }

        if (count($values) > 1) {
            // aggregate data
            if ($this->sensor_aggregator == 'avg') {
                $sensor_value = array_sum($values) / count($values);
            } else {
                // sum
                $sensor_value = array_sum($values);
            }
        } else {
            $sensor_value = Arr::first($values);
        }

        $sensor_value = Number::extract($sensor_value);

        if ($this->sensor_divisor) {
            $sensor_value = $sensor_value / $this->sensor_divisor;
        }

        $this->sensor_current = $sensor_value * $this->sensor_multiplier;

        return $this;
    }
}
