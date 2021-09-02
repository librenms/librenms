<?php
/**
 * enlogic-pdu.inc.php
 *
 * LibreNMS sensors voltage discovery module for enLOGIC PDU
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
foreach ($pre_cache['enlogic_pdu_input'] as $index => $data) {
    if (is_array($data)) {
        $oid = '.1.3.6.1.4.1.38446.1.3.4.1.6.' . $index;
        $descr = "Input Phase $index";
        $divisor = 1;
        $type = 'enlogic-pdu';
        $low_limit = $data['pduInputPhaseConfigVoltageLowerCriticalThreshold'];
        $low_warn = $data['pduInputPhaseConfigVoltageLowerWarningThreshold'];
        $high_limit = $data['pduInputPhaseConfigVoltageUpperCriticalThreshold'];
        $high_warn = $data['pduInputPhaseConfigVoltageUpperWarningThreshold'];
        $current = $data['pduInputPhaseStatusVoltage'];
        if ($current > 0) {
            discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', $low_limit, $low_warn, $high_warn, $high_limit, $current);
        }
    }
}
