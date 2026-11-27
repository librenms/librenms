<?php

/*
 * Printer.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Shared;

use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use LibreNMS\Util\StringHelpers;

class Printer extends \LibreNMS\OS
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml

        $device->serial = $device->serial ?? $this->getSerial() ?: null;
    }

    protected function getSerial()
    {
        return snmp_get($this->getDeviceArray(), 'prtGeneralSerialNumber.1', '-Oqv', 'Printer-MIB');
    }

    protected function parseDeviceId($data)
    {
        $vars = [];
        foreach (explode(';', (string) $data) as $pair) {
            if (! Str::contains($pair, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $pair);
            $vars[trim($key)] = $value;
        }

        return $vars;
    }

    /**
     * hrPrinterDetectedErrorState is a bit string, translate it to one state, multiple issues are a warning or critical
     */
    public function pollSensors(Collection $sensors): array
    {
        // value => critical, from the hrPrinterDetectedErrorState bits
        $states = [0 => false, 1 => false, 2 => true, 3 => false, 4 => true, 5 => true, 6 => true, 7 => true, 8 => true];
        $values = parent::pollSensors($sensors);

        foreach ($sensors->where('sensor_class', 'state')->where('sensor_type', 'hrPrinterDetectedErrorState') as $sensor) {
            $bits = StringHelpers::bitsToIndices((string) ($values[$sensor->sensor_id] ?? ''));
            $value = 0;
            $critical = false;

            foreach ($bits as $bit) {
                if (isset($states[$bit])) {
                    $value = $bit;
                    if ($states[$bit]) {
                        $critical = true;
                        break;
                    }
                }
            }

            // a state for each combination of bits is not possible
            $values[$sensor->sensor_id] = count($bits) > 1 ? ($critical ? 10 : 9) : $value;
        }

        return $values;
    }
}
