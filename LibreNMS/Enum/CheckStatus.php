<?php
/**
 * Check.php
 *
 * Nagios/monitoring style check status
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Enum;

class CheckStatus
{
    public const OK = 0;
    public const WARNING = 1;
    public const CRITICAL = 2;
    public const UNKNOWN = 3;

    public static function toState(string $name): int
    {
        switch ($name) {
            case 'ok':
                return self::OK;
            case 'warning':
                return self::WARNING;
            case 'critical':
                return self::CRITICAL;
            default:
                return self::UNKNOWN;
        }
    }
}
