<?php

/**
 * Huaweiups.php
 *
 * Huawei UPS
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

class Huaweiups extends OS
{
    /**
     * UPS-MIB frequencies are in hundredths, except on the UPS2000
     */
    protected function upsMibDivisor(SensorType $type, string $oid): int
    {
        if ($type === SensorType::Frequency) {
            return str_starts_with((string) $this->getDevice()->hardware, 'UPS2000') ? 10 : 100;
        }

        return parent::upsMibDivisor($type, $oid);
    }
}
