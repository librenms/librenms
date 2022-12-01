<?php
/**
 * Huaweiups.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use Illuminate\Support\Str;

class Huaweiups extends \LibreNMS\OS
{
    public function getUpsMibDivisor(string $oid): int
    {
        if (Str::startsWith($this->getDevice()->hardware, 'UPS2000')) {
            return parent::getUpsMibDivisor($oid);
        }

        return match ($oid) {
            'UPS-MIB::upsInputFrequency', 'UPS-MIB::upsOutputFrequency', 'UPS-MIB::upsBypassFrequency' => 100,
            default => parent::getUpsMibDivisor($oid),
        };
    }
}
