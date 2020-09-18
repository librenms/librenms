<?php
/**
 * ekinops.inc.php
 *
 * -Description-
 *
 * Ekinops Optical Networking
 * Gets information about the linecards for discovery.
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
 * Traps when Adva objects are created. This includes Remote User Login object,
 * Flow Creation object, and LAG Creation object.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2020 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

$pre_cache['ekishelf'] = snmpwalk_cache_multi_oid($device, 'mgnt2GigmBoardTable', [], 'EKINOPS-MGNT2-MIB', null, '-OQUbs');
