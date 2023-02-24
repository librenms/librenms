<?php
/**
 * ciscoepc.inc.php
 *
 * LibreNMS snr discovery module for Cisco EPC
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
foreach ($pre_cache['ciscoepc_docsIfSignalQualityTable'] as $index => $data) {
    if (is_numeric($data['docsIfSigQSignalNoise'])) {
        $descr = "Channel {$pre_cache['ciscoepc_docsIfDownstreamChannelTable'][$index]['docsIfDownChannelId']}";
        $oid = '.1.3.6.1.2.1.10.127.1.1.4.1.5.' . $index;
        $divisor = 10;
        $value = $data['docsIfSigQSignalNoise'];
        discover_sensor($valid['sensor'], 'snr', $device, $oid, 'docsIfSigQSignalNoise.' . $index, 'ciscoepc', $descr, $divisor, '1', null, null, null, null, $value);
    }
}
