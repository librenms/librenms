<?php
/**
 * PollWirelessChannelAsFrequency.php
 *
 * Helper function when a device returns channels instead of frequencies
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use Illuminate\Support\Collection;
use LibreNMS\Util\Wireless;

trait PollWirelessChannelAsFrequency
{
    /**
     * Poll wireless frequency as MHz
     * The returned array should be sensor_id => value pairs
     *
     * @param Collection $sensors Array of sensors needed to be polled
     * @return Collection of polled data
     */
    public function pollWirelessFrequency(Collection $sensors)
    {
        return $this->pollWirelessChannelAsFrequency($sensors);
    }

    /**
     * Poll a channel based OID, but return data in MHz
     *
     * @param Collection $sensors
     * @param callable $callback Function to modify the value before converting it to a frequency
     * @return Collection
     */
    public function pollWirelessChannelAsFrequency($sensors, $callback = null)
    {
        if (empty($sensors)) {
            return collect();
        }

        $oids = $sensors->pluck('oids', 'wireless_sensor_id');

        $snmp_data = snmp_get_multi_oid($this->getDevice(), $oids->flatten()->toArray());

        return $oids->map(function ($oid) use ($snmp_data, $callback) {
            $oid = current($oid);  // probably only one oid
            if (isset($callback)) {
                $channel = call_user_func($callback, $snmp_data[$oid]);
            } else {
                $channel = $snmp_data[$oid];
            }

            return Wireless::channelToFrequency($channel);
        });
    }
}
