<?php
/**
 * fortiswitch.inc.php
 *
 * LibreNMS mempools poller module for FortiSwitch
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

echo 'Fortigate MemPool';

$mempool['used']  = snmp_get($device, 'fsSysMemUsage.0', '-OvQ', 'FORTINET-FORTISWITCH-MIB');
$mempool['total'] = snmp_get($device, 'fsSysMemCapacity.0', '-OvQ', 'FORTINET-FORTISWITCH-MIB');
$mempool['free']  = ($mempool['total'] - $mempool['used']);
$mempool['perc'] = $mempool['total'] / $mempool['used'];

echo '(U: '.$mempool['used'].' T: '.$mempool['total'].' F: '.$mempool['free'].') ';
