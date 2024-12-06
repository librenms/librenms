<?php
/*
 *
 * LibreNMS storage poller module for Eltex-MES21xx
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
 */

if ($device['os'] == 'eltex-mes21xx') {
    $storage['units'] = 1024;
    $storage['free'] = snmp_get($device, 'rlFileFreeSizeOfFlash.0', '-Oqv', 'RADLAN-File');
    $storage['size'] = snmp_get($device, 'rlFileTotalSizeOfFlash.0', '-Oqv', 'RADLAN-File');
    $storage['used'] = $storage['size'] - $storage['free'];
}
