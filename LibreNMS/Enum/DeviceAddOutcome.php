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

enum DeviceAddOutcome
{
    case Added;
    case Known;
    case Unreachable;
    case Unpingable;
    case NoDns;
    case Excluded;
    case Error;

    public function symbol(): string
    {
        return match ($this) {
            self::Added => '+',
            self::Known => '*',
            self::Unreachable => '-',
            self::Unpingable => '.',
            self::NoDns => '~',
            self::Excluded => 'x',
            self::Error => 'E',
        };
    }

    public function description(): string
    {
        return __('commands.scan:network.outcomes.' . $this->name);
    }
}
