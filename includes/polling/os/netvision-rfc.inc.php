<?php
/**
 * remoteye4.inc.php
 *
 * LibreNMS os poller module for SOCOMEC Netvision with UPS-MIB support
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
 * @author     Neil Lathwood <gh+n@laf.io>
 */

$data = snmp_get_multi($device, ['upsIdentAgentSoftwareVersion.0'], '-OQUs', 'UPS-MIB');

$version  = str_replace('Net Vision ', '', $data[0]['upsIdentAgentSoftwareVersion']);
