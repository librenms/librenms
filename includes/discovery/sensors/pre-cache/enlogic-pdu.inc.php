<?php
/**
 * enlogic-pdu.inc.php
 *
 * LibreNMS sensors pre-cache discovery module for enLOGIC PDU
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
echo 'pduUnitStatusTable ';
$pre_cache['enlogic_pdu_status'] = snmpwalk_cache_oid($device, 'pduUnitStatusTable', [], 'ENLOGIC-PDU-MIB');

echo 'pduInputPhaseConfigTable ';
$pre_cache['enlogic_pdu_input'] = snmpwalk_cache_oid($device, 'pduInputPhaseConfigTable', [], 'ENLOGIC-PDU-MIB');
echo 'pduInputPhaseStatusTable ';
$pre_cache['enlogic_pdu_input'] = snmpwalk_cache_oid($device, 'pduInputPhaseStatusTable', $pre_cache['enlogic_pdu_input'], 'ENLOGIC-PDU-MIB');

echo 'pduCircuitBreakerConfigTable ';
$pre_cache['enlogic_pdu_circuit'] = snmpwalk_cache_oid($device, 'pduCircuitBreakerConfigTable', [], 'ENLOGIC-PDU-MIB');
echo 'pduCircuitBreakerStatusTable ';
$pre_cache['enlogic_pdu_circuit'] = snmpwalk_cache_oid($device, 'pduCircuitBreakerStatusTable', $pre_cache['enlogic_pdu_circuit'], 'ENLOGIC-PDU-MIB');
