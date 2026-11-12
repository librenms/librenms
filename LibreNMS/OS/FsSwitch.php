<?php

/**
 * Fs-switch.php
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
 * @copyright  2019 PipoCanaja
 * @author     PipoCanaja <pipocanaja@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\OS;

class FsSwitch extends OS
{
    public static function normalizeTransceiverValues($value): float
    {
        // Convert fixed-point integer thresholds to float
        $type = gettype($value);
        if ($type === 'integer') {
            // Thresholds are integers
            $value /= 100.0;
        }

        return $value;
    }

    public static function normalizeTransceiverValuesCurrent($value): float
    {
        $value = FsSwitch::normalizeTransceiverValues($value);
        $value *= 0.001; // mA to A

        return $value;
    }
}
