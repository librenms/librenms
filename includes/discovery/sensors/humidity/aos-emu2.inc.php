<?php
/**
 * aos-emu2.inc.php
 *
 * LibreNMS sensors humidity discovery module for APC EMU2
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
foreach ($pre_cache['emu2_temp'] as $id => $temp) {
    if (isset($temp['emsProbeStatusProbeHumidity']) && $temp['emsProbeStatusProbeHumidity'] > 0) {
        $index = $temp['emsProbeStatusProbeIndex'];
        $oid = '.1.3.6.1.4.1.318.1.1.10.3.13.1.1.6.' . $index;
        $descr = $temp['emsProbeStatusProbeName'];
        $low_limit = $temp['emsProbeStatusProbeMinHumidityThresh'];
        $low_warn_limit = $temp['emsProbeStatusProbeLowHumidityThresh'];
        $high_limit = $temp['emsProbeStatusProbeMaxHumidityThresh'];
        $high_warn_limit = $temp['emsProbeStatusProbeHighHumidityThresh'];
        $current = $temp['emsProbeStatusProbeHumidity'];
        discover_sensor($valid['sensor'], 'humidity', $device, $oid, $index, 'aos-emu2', $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
    }
}
