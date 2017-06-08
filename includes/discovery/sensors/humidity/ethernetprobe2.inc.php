<?php
/**
 * etherprobe2.inc.php
 *
 * LibreNMS temperature sensor discover module for Atal EtherProbe2
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

foreach ($pre_cache['ethernetprobe2_sensorProbeHumidityTable'] as $index => $data) {
    if ($data['sensorProbeHumidityOnline'] === 'online') {
        if (is_numeric($data['sensorProbeHumidityRaw'])) {
            $oid = '.1.3.6.1.4.1.3854.1.2.2.1.17.1.13.' . $index;
            $descr = $data['sensorProbeHumidityDescription'];
            $value = $data['sensorProbeHumidityRaw'];
            $low_limit = $data['sensorProbeHumidityLowCritical'];
            $low_warn_limit = $data['sensorProbeHumidityLowWarning'];
            $warn_limit = $data['sensorProbeHumidityHighWarning'];
            $high_limit = $data['sensorProbeHumidityHighCritical'];
            discover_sensor($valid['sensor'], 'humidity', $device, $oid, $index, 'ethernetprobe2', $descr, 1, '1', $low_limit, $low_warn_limit, $warn_limit, $high_limit, $value);
        }
    }
}
