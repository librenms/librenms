<?php
/**
 * heliosip.php
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
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 */
// SNMPv2-SMI::enterprises.6530.11.1.0 = STRING: "2N IP Force"
// SNMPv2-SMI::enterprises.6530.11.4.0 = STRING: "2.22.0.31.8"
// SNMPv2-SMI::enterprises.6530.11.3.0 = STRING: "54-0880-2424"
$data = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.6530.11.1.0', '.1.3.6.1.4.1.6530.11.3.0', '.1.3.6.1.4.1.6530.11.4.0'], '-OUQn');
$hardware = isset($data['.1.3.6.1.4.1.6530.11.1.0']) ? $data['.1.3.6.1.4.1.6530.11.1.0'] : '';
$version = isset($data['.1.3.6.1.4.1.6530.11.4.0']) ? $data['.1.3.6.1.4.1.6530.11.4.0'] : '';
$serial = isset($data['.1.3.6.1.4.1.6530.11.3.0']) ? $data['.1.3.6.1.4.1.6530.11.3.0'] : '';
