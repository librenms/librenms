<?php

/**
 * AristaEos.php
 *
 * Arista EOS
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Sensor;
use Illuminate\Support\Str;
use LibreNMS\Util\UserFuncHelper;
use SnmpQuery;

class AristaEos extends Shared\Arista
{
    /**
     * Unnamed entities get a name from their description and the unit they belong to
     */
    protected function entitySensorDescr(int $index, array $entities): string
    {
        $descr = parent::entitySensorDescr($index, $entities);
        if ($descr !== '') {
            return $descr;
        }

        $descr = $entities[$index]['entPhysicalDescr'] ?? '';
        if (preg_match('/(Input|Output) (voltage|current) sensor/i', $descr) || preg_match('/^(Power Supply|Hotspot|Inlet|Board)/i', $descr)) {
            $unit = $entities[substr_replace((string) $index, '000', -3)]['entPhysicalDescr'] ?? '';
            $descr = ucwords($unit) . ' ' . preg_replace('/(Voltage|Current|Power Supply) Sensor$/i', '', ucwords($descr));
        }

        return preg_replace('/(temp|temperature) sensor$/i', '', $descr);
    }

    /**
     * DOM power is converted to dBm, limits come from ARISTA-ENTITY-SENSOR-MIB and sensors are grouped
     */
    protected function entitySensor(Sensor $sensor, array $entry): ?Sensor
    {
        // DOM power is reported in 0.1 µW, polling converts it the same way
        $toDbm = fn ($value) => UserFuncHelper::apply('uw_to_dbm', $value / 10);
        $limitDbm = fn ($value) => is_numeric($dbmValue = $toDbm($value)) ? round($dbmValue, 2) : null;
        $dbm = $sensor->sensor_class == 'power' && preg_match('/DOM [RT]x Power/i', $sensor->sensor_descr);

        if ($dbm) {
            $sensor->fill([
                'sensor_class' => 'dbm',
                'sensor_current' => $toDbm($entry['entPhySensorValue'] ?? 0),
                'sensor_divisor' => 10,
                'sensor_multiplier' => 1,
                'user_func' => 'uw_to_dbm',
            ]);
        }

        $thresholds = SnmpQuery::cache()->hideMib()->walk('ARISTA-ENTITY-SENSOR-MIB::aristaEntSensorThresholdTable')->table(1)[$sensor->sensor_index] ?? [];
        foreach ([
            'sensor_limit_low_warn' => 'aristaEntSensorThresholdLowWarning',
            'sensor_limit_low' => 'aristaEntSensorThresholdLowCritical',
            'sensor_limit_warn' => 'aristaEntSensorThresholdHighWarning',
            'sensor_limit' => 'aristaEntSensorThresholdHighCritical',
        ] as $limit => $oid) {
            $value = $thresholds[$oid] ?? null;
            if (is_numeric($value) && abs($value) != 1000000000) {
                $sensor->$limit = $dbm ? $limitDbm($value) : $value * $sensor->sensor_multiplier / $sensor->sensor_divisor;
            }
        }

        $descr = $sensor->sensor_descr;
        if (preg_match('/DOM /i', $descr)) {
            $sensor->group = 'SFPs';
        } elseif (preg_match('/PwrCon/', $descr)) {
            $unit = explode(' ', $descr)[0];
            $sensor->group = preg_match('/PwrCon[0-9]/', $unit) ? $unit : preg_replace('/PwrCon/i', '', $unit);
            $sensor->sensor_descr = preg_replace('/^.*?(PwrCon)[0-9]*/i', '', $descr);
        } elseif (preg_match('/^(Trident.*|Jericho[0-9]|FM6000)/i', $descr)) {
            $sensor->group = 'Platform';
        } elseif (preg_match('/^(Power|PSU)/i', $descr)) {
            $sensor->group = 'PSUs';
        } else {
            $sensor->group = 'System';
            $sensor->sensor_descr = Str::replaceLast('temp sensor', '', $descr);
        }

        return $sensor;
    }
}
