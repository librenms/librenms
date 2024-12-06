<?php
/*
 * LibreNMS storage discovery module for Eltex-MES24xx
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 * @copyright  2024 Peca Nesovanovic
 */

$fstype = 'Flash';
$descr = 'Internal Flash';
$units = 1;
$index = 0;
$total = 100;
$used = SnmpQuery::hideMib()->get('ARICENT-ISS-MIB::issSwitchCurrentFlashUsage.0')->value();

if (is_numeric($used)) {
    discover_storage($valid_storage, $device, $index, $fstype, 'eltex-mes24xx', $descr, $total, $units, $used);
}
