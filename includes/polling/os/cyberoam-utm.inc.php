<?php
/**
 * cyberoam-utm.inc.php
 *
 * LibreNMS os poller module for Cyberoam-UTM
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
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

$cyberoam_data = snmp_get_multi_oid($device, ['applianceModel.0', 'cyberoamVersion.0', 'applianceKey.0'], '-OQUs', 'CYBEROAM-MIB');

$hardware = $cyberoam_data['applianceModel.0'];
$version  = $cyberoam_data['cyberoamVersion.0'];
$serial   = $cyberoam_data['applianceKey.0'];

unset(
    $cyberoam_data
);
