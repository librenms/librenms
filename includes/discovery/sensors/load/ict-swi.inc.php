<?php
/**
 * ict-swi.inc.php
 *
 * LibreNMS load sensor discovery module for ICT Sine Wave Inverter
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
 * @copyright  2017 Lorenzo Zafra
 * @author     Lorenzo Zafra<zafra@ualberta.ca>
 */

// Inverter Load
$inverterLoad = (int)(snmp_get($device, 'inverterPower.0', '-Oqv', 'ICT-SINE-WAVE-INVERTER-MIB'));
if (!empty($inverterLoad)) {
    $oid = '.1.3.6.1.4.1.39145.12.8.0';
    $descr = 'Inverter Load';
    $type = 'ict-swi';
    
    discover_sensor($valid['sensor'], 'load', $device, $oid, 0, $type, $descr, 1, '1', null, null, null, null, $inverterLoad);
}
