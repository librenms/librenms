<?php

/**
 * DeviceAddOutcome.php
 *
 * The outcome of trying to add a device that was found by a scan or a neighbor protocol.
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

namespace LibreNMS\Enum;

/**
 * The values are the exit codes of scan:host. 1 is the exit code of a crashed process, so it is an error.
 */
enum DeviceAddOutcome: int
{
    case Error = 1;
    case Added = 2;
    case Known = 3;
    case Unpingable = 4;
    case Unreachable = 5;
    case Excluded = 6;
    case NoDns = 7;

    public function symbol(): string
    {
        return match ($this) {
            self::Error => 'E',
            self::Added => '+',
            self::Known => '*',
            self::Unpingable => '.',
            self::Unreachable => '-',
            self::Excluded => 'x',
            self::NoDns => '~',
        };
    }

    public function description(): string
    {
        return __('commands.scan:network.outcomes.' . $this->name);
    }
}
