<?php
/**
 * WirelessRsrqDiscovery.php
 *
 * Discover wireless RSRQ (Quality of the received signal,) sensors in dB
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
 * @copyright  2019 Jozef Rebjak
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 */

namespace LibreNMS\Interfaces\Discovery\Sensors;

interface WirelessRsrqDiscovery
{
    /**
     * Discover wireless RSRQ (Quality of the received signal,). This is in dB. Type is rsrq.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array
     */
    public function discoverWirelessRsrq();
}
