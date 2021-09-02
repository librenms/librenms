<?php
/**
 * WirelessChannelDiscovery.php
 *
 * Discover Wireless channel in channel number. Type is channel.
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

interface WirelessChannelDiscovery
{
    /**
     * Discover Wireless channel in channel number. Type is channel.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessChannel();
}
