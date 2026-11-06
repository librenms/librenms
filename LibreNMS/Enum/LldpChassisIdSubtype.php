<?php

/**
 * LldpChassisIdSubtype.php
 *
 * How the chassis id advertised by a neighbor should be interpreted (LLDP-MIB LldpChassisIdSubtype)
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

enum LldpChassisIdSubtype: int
{
    case ChassisComponent = 1;
    case InterfaceAlias = 2;
    case PortComponent = 3;
    case MacAddress = 4;
    case NetworkAddress = 5;
    case InterfaceName = 6;
    case Local = 7;

    /**
     * Parse a subtype from snmp, unknown values are treated as locally assigned
     */
    public static function parse(int|string|null $subtype): self
    {
        return self::tryFrom((int) $subtype) ?? self::Local;
    }
}
