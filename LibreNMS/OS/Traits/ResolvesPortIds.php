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
    /** @var string[] */
    private $ifIndexToNameMap;

    /**
     * Figure out the port_id from the BRIDGE-MIB::dot1dBasePort
     *
     * @param  int|string  $port
     * @return int
     */
    public function basePortToId($port): int
    {
        return $this->basePortToPortIdMap()[$port] ?? 0;
    }

    /**
     * Figure out the port_id from IF-MIB::ifIndex
     *
     * @param  int|string  $ifIndex
     * @return int
     */
    public function ifIndexToId($ifIndex): int
    {
        return $this->ifIndexToPortIdMap()[$ifIndex] ?? 0;
    }

    /**
     * Get IF-MIB::ifName from IF-MIB::ifIndex
     *
     * @param  int|string  $ifIndex
     * @return string
     */
    public function ifIndexToName($ifIndex): string
    {
        if ($this->ifIndexToNameMap === null) {
            $this->ifIndexToNameMap = $this->getDevice()->ports()->pluck('ifName', 'ifIndex')->all();
        }

        return $this->ifIndexToNameMap[$ifIndex] ?? '';
    }

    private function ifIndexToPortIdMap(): array
    {
        if ($this->ifIndexPortIdMap === null) {
            $this->ifIndexPortIdMap = $this->getDevice()->ports()->pluck('port_id', 'ifIndex')->all();
        }

        return $this->ifIndexPortIdMap;
    }

    private function basePortToPortIdMap(): array
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
