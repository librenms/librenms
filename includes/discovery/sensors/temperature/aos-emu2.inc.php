<?php
/**
 * aos-emu2.inc.php
 *
 * LibreNMS sensors temp discovery module for APC EMU2
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
    if (isset($temp['emsProbeStatusProbeTemperature']) && $temp['emsProbeStatusProbeTemperature'] > 0) {
        $index = $temp['emsProbeStatusProbeIndex'];
        $oid = '.1.3.6.1.4.1.318.1.1.10.3.13.1.1.3.' . $index;
        $descr = $temp['emsProbeStatusProbeName'];
        $low_limit = fahrenheit_to_celsius($temp['emsProbeStatusProbeMinTempThresh'], $pre_cache['emu2_temp_scale']);
        $low_warn_limit = fahrenheit_to_celsius($temp['emsProbeStatusProbeLowTempThresh'], $pre_cache['emu2_temp_scale']);
        $high_limit = fahrenheit_to_celsius($temp['emsProbeStatusProbeMaxTempThresh'], $pre_cache['emu2_temp_scale']);
        $high_warn_limit = fahrenheit_to_celsius($temp['emsProbeStatusProbeHighTempThresh'], $pre_cache['emu2_temp_scale']);
        $current = fahrenheit_to_celsius($temp['emsProbeStatusProbeTemperature'], $pre_cache['emu2_temp_scale']);
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'aos-emu2', $descr, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
    }
}
