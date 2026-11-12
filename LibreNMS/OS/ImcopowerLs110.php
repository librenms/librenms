<?php

/**
 * ImcopowerLs110.php
 *
 * IMCO Power LS/PS helpers for YAML discovery.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace LibreNMS\OS;

use LibreNMS\OS;

class ImcopowerLs110 extends OS
{
    /**
     * Map IMCO opto binary inputs into state_translations.state_value range.
     *
     * MIB: activeSignalPresent(0), noSignal(32768). state_value is a signed
     * smallint (max 32767), so 32768 must be remapped before sync.
     */
    public static function optoSignal(int|string $value): int
    {
        $value = (int) $value;

        return $value === 32768 ? 1 : $value;
    }
}
