<?php

/**
 * PowerState.php
 *
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
 */

namespace LibreNMS\Enum;

abstract class PowerState
{
    const OFF = 0;
    const ON = 1;
    const SUSPENDED = 2;
    const UNKNOWN = 3;

    public static function parse(string $state): int
    {
        return match (strtolower($state)) {
            'powered off', 'powerdoff', 'shut off', 'stopped' => self::OFF,
            'powered on', 'poweredon', 'running' => self::ON,
            'suspended', 'paused' => self::SUSPENDED,
            default => self::UNKNOWN,
        };
    }
}
