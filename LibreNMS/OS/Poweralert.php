<?php

/**
 * Poweralert.php
 *
 * Tripp Lite PowerAlert
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
use SnmpQuery;

class Poweralert extends OS
{
    /**
     * UPS-MIB units changed between card firmware versions
     */
    protected function upsMibDivisor(SensorType $type, string $oid): int
    {
        $version = trim((string) SnmpQuery::cache()->get('.1.3.6.1.4.1.850.100.1.1.4.0')->value(), '"'); // tlUpsSnmpCardSerialNum

        if ($type === SensorType::Current || $type === SensorType::Frequency) {
            if (version_compare($version, '12.06.0068', '>=') || version_compare($version, '12.04.0055', '=')) {
                return 10;
            }

            if (version_compare($version, '12.04.0056', '>=')) {
                return 1;
            }
        } elseif ($type === SensorType::Load) {
            return version_compare($version, '12.06.0064', '=') ? 10 : 1;
        }

        return parent::upsMibDivisor($type, $oid);
    }
}
