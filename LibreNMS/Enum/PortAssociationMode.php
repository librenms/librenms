<?php
/**
 * PortAssociationMode.php
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Enum;

class PortAssociationMode
{
    const ifIndex = 1;
    const ifName = 2;
    const ifDescr = 3;
    const ifAlias = 4;

    /**
     * Get mode names keyed by id
     *
     * @return string[]
     */
    public static function getModes(): array
    {
        return [
            self::ifIndex => 'ifIndex',
            self::ifName => 'ifName',
            self::ifDescr => 'ifDescr',
            self::ifAlias => 'ifAlias',
        ];
    }

    /**
     * Translate a named port association mode to an integer for storage
     *
     * @param  string  $name
     * @return int|null
     */
    public static function getId(string $name): ?int
    {
        $names = array_flip(self::getModes());

        return $names[$name] ?? null;
    }

    /**
     * Get name of given port association mode id
     *
     * @param  int  $id
     * @return string|null
     */
    public static function getName(int $id): ?string
    {
        $modes = self::getModes();

        return $modes[$id] ?? null;
    }
}
