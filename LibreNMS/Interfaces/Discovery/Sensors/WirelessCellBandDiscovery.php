<?php
/**
 * WirelessCellBandDiscovery.php
 *
 * Discover wireless Cellular Operating Band. This is in band number
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
 * @link       http://librenms.org
 * @copyright  2021 Janno Schouwenburg
 * @author     Janno Schouwenburg <handel@janno.com>
 */

namespace LibreNMS\Interfaces\Discovery\Sensors;

interface WirelessCellBandDiscovery
{
    /**
     * Discover wireless Cellular Operating Band. This is in band number. Type is cellband.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessCellBand();
}
