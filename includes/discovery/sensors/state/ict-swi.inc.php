<?php
/**
 * ict-swi.inc.php
 *
 * LibreNMS status sensor discovery module for ICT Sine Wave Inverter
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

$oids = array(
  "inverterStatus.0" => [
    "oid" => ".1.3.6.1.4.1.39145.12.9.0",
    "descr" => "Inverter Status",
    "state_name" => "inverterStatus",
    "states" => [
      ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'enabled'],
      ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'disabled'],
    ]
  ],
  "inverterControl.0" => [
    "oid" => ".1.3.6.1.4.1.39145.12.10.0",
    "descr" => "Inverter Control",
    "state_name" => "inverterControl",
    "states" => [
      ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'enabled'],
      ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'disabled'],
    ]
  ],
  "transferRelayStatus.0" => [
    "oid" => ".1.3.6.1.4.1.39145.12.11.0",
    "descr" => "Transfer Relay Status",
    "state_name" => "transferRelayStatus",
    "states" => [
      ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'inverter'],
      ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'grid'],
    ]
  ]
);

foreach ($oids as $oid => $info) {
  $data = (int)(snmpget($device, $oid, '-0qv', 'ICT-SINE-WAVE-INVERTER-MIB'));
  if($data >= 0) {
    create_state_index($info["state_name"], $info["states"]);
    discover_sensor($valid['sensor'], 'state', $device, $info["oid"], 0, $info["state_name"], $info["descr"], 1, 1, null, null, null, null, $data, 'snmp', 0);
    create_sensor_to_state_index($device, $info["state_name"], 0);
  }
}
