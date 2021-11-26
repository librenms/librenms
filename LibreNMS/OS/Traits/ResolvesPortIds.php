<?php
/*
 * ResolvesPortIds.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

trait ResolvesPortIds
{
    /**
     * @var array
     */
    private $ifIndexPortIdMap;
    /**
     * @var array
     */
    private $basePortIdMap;

    /**
     * @param  int|string  $port
     * @return  int
     */
    public function basePortToId($port): int
    {
        return $this->basePortToPortIdMap()[$port] ?? 0;
    }

    /**
     * @param  int|string  $ifIndex
     * @return  int
     */
    public function ifIndexToId($ifIndex): int
    {
        return $this->ifIndexToPortIdMap()[$ifIndex] ?? 0;
    }

    public function ifIndexToPortIdMap(): array
    {
        if ($this->ifIndexPortIdMap === null) {
            $this->ifIndexPortIdMap = $this->getDevice()->ports()->pluck('port_id', 'ifIndex')->all();
        }

        return $this->ifIndexPortIdMap;
    }

    public function basePortToPortIdMap(): array
    {
        if ($this->basePortIdMap === null) {
            $base = $this->getCacheByIndex('BRIDGE-MIB::dot1dBasePortIfIndex');
            $this->basePortIdMap = array_map(function ($ifIndex) {
                return $this->ifIndexToId($ifIndex);
            }, $base);
        }

        return $this->basePortIdMap;
    }
}
