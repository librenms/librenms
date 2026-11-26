<?php

/**
 * ApcMgeups.php
 *
 * APC MGE UPS
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

class ApcMgeups extends OS
{
    /**
     * UPS-MIB voltages are in tenths
     */
    protected function upsMibDivisor(SensorType $type, string $oid): int
    {
        return $type === SensorType::Voltage ? 10 : parent::upsMibDivisor($type, $oid);
    }
}
