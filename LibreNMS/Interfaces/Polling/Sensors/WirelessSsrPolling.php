<?php
/**
 * WirelessSsrPolling.php
 *
 * Custom polling interface for wireless Signal Strength Ratio
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
 * @copyright  2017 Paul Heinrichs
 * @author     Paul Heinrichs <pdheinrichs@gmail.com>
 */

namespace LibreNMS\Interfaces\Polling\Sensors;

interface WirelessSsrPolling
{
    /**
     * Poll wireless SSR in dB
     * The returned array should be sensor_id => value pairs
     *
     * @param array $sensors Array of sensors needed to be polled
     * @return array of polled data
     */
    public function pollWirelessSsr(array $sensors);
}
