<?php
/**
 * UpsMib.php
 *
 * RFC1628 UPS-MIB support
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

trait UpsMib
{
    public function getUpsMibDivisor(string $oid): int
    {
        // UPS-MIB Defaults
        return match ($oid) {
            'UPS-MIB::upsBatteryVoltage' => 10,
            'UPS-MIB::upsBatteryCurrent' => 10,
            'UPS-MIB::upsInputFrequency' => 10,
            'UPS-MIB::upsInputCurrent' => 10,
            'UPS-MIB::upsOutputFrequency' => 10,
            'UPS-MIB::upsOutputCurrent' => 10,
            'UPS-MIB::upsBypassFrequency' => 10,
            'UPS-MIB::upsBypassCurrent' => 10,
            'UPS-MIB::upsSecondsOnBattery' => 60,
            default => 1,
        };
    }
}
