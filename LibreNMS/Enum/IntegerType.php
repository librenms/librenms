<?php

/**
 * IntegerType.php
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Enum;

enum IntegerType
{
    case Int8;
    case Int16;
    case Int32;
    case Int64;
    case Uint8;
    case Uint16;
    case Uint32;
    case Uint64;

    public function maxValue(): int
    {
        return match ($this) {
            self::Int8 => 127,
            self::Int16 => 32767,
            self::Int32 => 2147483647,
            self::Int64 => 4611686018427387903,
            self::Uint8 => 255,
            self::Uint16 => 65535,
            self::Uint32 => 4294967295,
            self::Uint64 => 9223372036854775807,
        };
    }

    public function isSigned(): bool
    {
        return match ($this) {
            self::Int8,self::Int16,self::Int32,self::Int64 => true,
            self::Uint8,self::Uint16,self::Uint32,self::Uint64 => false,
        };
    }
}
