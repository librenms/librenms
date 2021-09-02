<?php
/**
 * enlogic-pdu.inc.php
 *
 * LibreNMS sensors power discovery module for enLOGIC PDU
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
foreach ($pre_cache['enlogic_pdu_status'] as $index => $data) {
    if (is_array($data)) {
        $current = $data['pduUnitStatusActivePower'];
        $descr = "Active power #$index";
        $oid = '.1.3.6.1.4.1.38446.1.2.4.1.4.' . $index;
        if ($current > 0) {
            discover_sensor($valid['sensor'], 'power', $device, $oid, $index, 'enlogic-pdu', $descr, 1, 1, null, null, null, null, $current);
        }
    }
}

foreach ($pre_cache['enlogic_pdu_input'] as $index => $data) {
    if (is_array($data)) {
        $current = $data['pduInputPhaseStatusActivePower'];
        $tmp_index = 'pduInputPhaseStatusActivePower.' . $index;
        $descr = 'Input Phase #' . $index;
        $oid = '.1.3.6.1.4.1.38446.1.3.4.1.7.' . $index;
        if ($current > 0) {
            discover_sensor($valid['sensor'], 'power', $device, $oid, $tmp_index, 'enlogic-pdu', $descr, 1, 1, null, null, null, null, $current);
        }
    }
}
