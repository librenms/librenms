<?php

/*
 * MaintenanceAlertBehavior.php
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 */

namespace LibreNMS\Enum;

enum MaintenanceAlertBehavior: int
{
    case ANY = 0;
    case SKIP = 1;
    case MUTE = 2;
    case RUN = 3;

    public static function is_valid_option($value)
    {
        $valid_options = [
            self::SKIP->value,
            self::MUTE->value,
            self::RUN->value,
        ];

        return in_array((int) $value, $valid_options);
    }

    public static function get_value_or_fallback($value, $fallback)
    {
        // the fallback value needs to be validated first to avoid config nonsense
        $valid_fallback = self::is_valid_option($fallback)
            ? $fallback
            : self::SKIP->value;

        return self::is_valid_option($value)
            ? (int) $value
            : (int) $valid_fallback;
    }
}
