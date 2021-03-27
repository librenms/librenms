<?php
/**
 * WirelessCellPolling.php
 *
 * Custom polling interface for Wireless Cell. Type is cell.
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

namespace LibreNMS\Interfaces\Polling\Sensors;

interface WirelessCellPolling
{
    /**
     * Poll wireless Cellular Cell. Type is cell.
     * The returned array should be sensor_id => value pairs
     *
     * @param array $sensors Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessCell(array $sensors);
}
