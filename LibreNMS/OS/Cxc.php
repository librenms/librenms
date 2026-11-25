<?php

/**
 * Cxc.php
 *
 * CXC
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

use LibreNMS\Enum\SensorType;
use LibreNMS\OS;

class Cxc extends OS
{
    /**
     * UPS-MIB input voltages are in tenths
     */
    protected function upsMibDivisor(SensorType $type, string $oid): int
    {
        if ($type === SensorType::Voltage && str_starts_with($oid, '.1.3.6.1.2.1.33.1.3.3.1.3')) {
            return 10;
        }

        return parent::upsMibDivisor($type, $oid);
    }
}
