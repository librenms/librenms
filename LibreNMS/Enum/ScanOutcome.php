<?php

/**
 * ScanOutcome.php
 *
 * Enum representing outcomes of scanning a network host.
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
 * The values are the exit codes of scan:host, 1 is the generic failure exit code, so it is an error.
 */
enum ScanOutcome: int
{
    case Undefined = 0;
    case Error = 1;
    case Added = 2;
    case Unpingable = 3;
    case Known = 4;
    case Failed = 5;
    case Excluded = 6;
    case Terminated = 7;
    case NoDns = 8;

    public function symbol(): string
    {
        return match ($this) {
            self::Added => '+',
            self::Unpingable => '.',
            self::Known => '*',
            self::Failed => '-',
            self::NoDns => '~',
            self::Error => 'E',
            default => '?',
        };
    }
}
