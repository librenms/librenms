<?php
/**
 * hpe-ipdu.inc.php
 *
 * LibreNMS os poller module for HPE iPDU
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
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$hpe_ipdu_data = snmp_get_multi_oid($device, ['mpduFirmwareVersion.0', 'mpduSerialNumber.0', 'mpduModel.0'], '-OUQs', 'CPQPOWER-MIB');

$serial = trim($hpe_ipdu_data['mpduSerialNumber.1'], '"');
$version = trim($hpe_ipdu_data['mpduFirmwareVersion.1'], '"');
$hardware = trim($hpe_ipdu_data['mpduModel.1'], '"');

unset($hpe_ipdu_data);
