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
    case int8;
    case int16;
    case int32;
    case int64;
    case uint8;
    case uint16;
    case uint32;
    case uint64;

    public function maxValue(): int
    {
        return match ($this) {
            self::int8 => 127,
            self::int16 => 32767,
            self::int32 => 2147483647,
            self::int64 => 4611686018427387903,
            self::uint8 => 255,
            self::uint16 => 65535,
            self::uint32 => 4294967295,
            self::uint64 => 9223372036854775807,
        };
    }

    public function isSigned(): bool
    {
        return match ($this) {
            self::int8,self::int16,self::int32,self::int64 => true,
            self::uint8,self::uint16,self::uint32,self::uint64 => false,
        };
    }
}
